<?php
/**
 * @package Goomento_PageBuilder
 * @link https://github.com/Goomento/PageBuilder
 */

declare(strict_types=1);

namespace Goomento\PageBuilder\Builder\Managers;

use Exception;
use Goomento\PageBuilder\Api\Data\BuildableContentInterface;
use Goomento\PageBuilder\Builder\Sources\Local;
use Goomento\PageBuilder\Builder\Base\AbstractDocument;
use Goomento\PageBuilder\Builder\Modules\Ajax;
use Goomento\PageBuilder\Builder\DocumentTypes\Page;
use Goomento\PageBuilder\Builder\DocumentTypes\Section;
use Goomento\PageBuilder\Builder\DocumentTypes\Template;
use Goomento\PageBuilder\Helper\HooksHelper;
use Goomento\PageBuilder\Helper\BuildableContentHelper;
use Goomento\PageBuilder\Helper\ObjectManagerHelper;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Documents
{
    /**
     * Registered types.
     *
     * Holds the list of all the registered types.
     *
     *
     * @var AbstractDocument[]
     */
    protected $types = [];

    /**
     * Registered documents.
     *
     * Holds the list of all the registered documents.
     *
     *
     * @var AbstractDocument[]
     */
    protected $documents = [];

    /**
     * Documents manager constructor.
     *
     * Initializing the SagoTheme documents manager.
     *
     */
    public function __construct()
    {
        HooksHelper::addAction('pagebuilder/documents/register', [ $this, 'registerDefaultTypes' ], 0);
        HooksHelper::addAction('pagebuilder/ajax/register_actions', [ $this, 'registerAjaxActions' ]);
    }

    /**
     * Register ajax actions.
     *
     * Process ajax action handles when saving data and discarding changes.
     *
     * @param Ajax $ajaxManager An instance of the ajax manager.
     * @throws LocalizedException
     * @throws Exception
     */
    public function registerAjaxActions(Ajax $ajaxManager)
    {
        $ajaxManager->registerAjaxAction('save_builder', [ $this, 'ajaxSave' ]);
        $ajaxManager->registerAjaxAction('discard_changes', [ $this, 'ajaxDiscardChanges' ]);
    }

    /**
     * Register default types.
     *
     * Registers the default document types.
     *
     */
    public function registerDefaultTypes()
    {
        $defaultTypes = [
            'page' => Page::class,
            'template' => Template::class,
            'section' => Section::class,
        ];

        foreach ($defaultTypes as $type => $class) {
            $this->registerDocumentType($type, $class);
        }
    }

    /**
     * @param $type
     * @param $class
     * @return $this
     */
    public function registerDocumentType($type, $class)
    {
        $this->types[ $type ] = $class;

        if ($class::getProperty('register_type')) {
            Local::addTemplateType($type);
        }

        return $this;
    }

    /**
     * Get document.
     *
     * Retrieve the document data based on a content ID.
     *
     *
     * @param BuildableContentInterface $content
     * @param bool $fromCache Optional. Whether to retrieve cached data. Default is true.
     *
     * @return false|AbstractDocument AbstractDocument data or false if content ID was not entered.
     */
    public function getByContent(BuildableContentInterface $content, $fromCache = true)
    {
        $this->registerTypes();
        $cacheKey = $content->getUniqueIdentity();
        if (!$fromCache || ! isset($this->documents[$cacheKey])) {
            $docTypeClass = $this->getDocumentType( $content->getOriginContent()->getType() );
            $this->documents[$cacheKey] = ObjectManagerHelper::create($docTypeClass, [
                    'data' => [
                        'id' => $content->getId(),
                        'model' => $content,
                    ]
                ]
            );
        }

        return $this->documents[$cacheKey];
    }

    /**
     * @param $type
     * @return false|AbstractDocument
     */
    public function getDocumentType($type)
    {
        $types = $this->getDocumentTypes();
        if (isset($types[ $type ])) {
            return $types[ $type ];
        }

        return false;
    }

    /**
     * Get document types.
     *
     * Retrieve the all the registered document types.
     *
     * @return AbstractDocument[] All the registered document types.
     *
     */
    public function getDocumentTypes()
    {
        $this->registerTypes();

        return $this->types;
    }

    /**
     * Get document types with their properties.
     *
     * @return array A list of properties arrays indexed by the type.
     */
    public function getTypesProperties()
    {
        $typesProperties = [];

        foreach ($this->getDocumentTypes() as $type => $class) {
            $typesProperties[ $type ] = $class::getProperties();
        }
        return $typesProperties;
    }

    /**
     * Create a document.
     *
     * Create a new document using any given parameters.
     *
     *
     * @param string $type AbstractDocument type.
     * @param array $data An array containing the post data.
     * @return AbstractDocument The type of the document.
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function create(string $type, array $data = [])
    {
        $class = $this->getDocumentType($type);

        if (!$class) {
            throw new Exception(
                sprintf('Type %s does not exist.', $type)
            );
        }

        if (!isset($data['status'])) {
            $data['status'] = BuildableContentInterface::STATUS_PENDING;
        }

        $data['type'] = $type;

        $content = BuildableContentHelper::createContent($data);

        /** @var AbstractDocument $document */
        $document = ObjectManagerHelper::create($class, [
            'data' => [
                'id' => $content->getId(),
                'model' => $content
            ]]
        );

        $document->save([]);

        return $document;
    }

    /**
     * Save document data using ajax.
     *
     * Save the document on the builder using ajax, when saving the changes, and refresh the editor.
     *
     *
     *
     * @throws Exception If current user don't have permissions to edit the post or the post is not using SagoTheme.
     *
     * @return array The document data after saving.
     */
    public function ajaxSave(array $requestData, BuildableContentInterface $buildableContent)
    {

        $buildableContent->setData('status', $requestData['status'] ?? BuildableContentInterface::STATUS_REVISION);

        $document = $this->getByContent( $buildableContent );

        $data = [
            'elements' => $requestData['elements'],
            'settings' => $requestData['settings'],
        ];

        $document->save( $data );

        $returnData = [
            'config' => [
                'document' => [
                    'last_edited' => $document->getLastEdited(), // Should remove this
                    'date' => date(DATE_ATOM, strtotime($buildableContent->getUpdateTime())),
                    'urls' => [
                        'system_preview' => $document->getSystemPreviewUrl(),
                    ],
                ],
            ],
        ];

        /**
         * Returned documents ajax saved data.
         *
         * Filters the ajax data returned when saving the post on the builder.
         *
         *
         * @param array    $returnData The returned data.
         * @param AbstractDocument $document    The document instance.
         */
        return HooksHelper::applyFilters('pagebuilder/documents/ajax_save/return_data', $returnData, $document);
    }

    /**
     * Ajax discard changes.
     *
     * Load the document data from an autosave, deleting unsaved changes.
     *
     *
     * @param $request
     *
     * @return bool True if changes discarded, False otherwise.
     */
    public function ajaxDiscardChanges($request)
    {
        return $success = false;
    }

    /**
     * @return void
     */
    private function registerTypes()
    {
        if (! HooksHelper::didAction('pagebuilder/documents/register')) {
            /**
             * Register SagoTheme documents.
             *
             *
             * @param Documents $this The document manager instance.
             */
            HooksHelper::doAction('pagebuilder/documents/register', $this);
        }
    }
}
