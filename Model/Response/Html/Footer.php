<?php
/**
 * @package Goomento_PageBuilder
 * @link https://github.com/Goomento/PageBuilder
 */

declare(strict_types=1);

namespace Goomento\PageBuilder\Model\Response\Html;

use Goomento\PageBuilder\Api\ModifierInterface;
use Goomento\PageBuilder\Helper\HooksHelper;
use Goomento\PageBuilder\Helper\StateHelper;

class Footer implements ModifierInterface
{
    /**
     * Add JS/CSS to footer of specific page
     *
     * @param $data
     * @return string
     */
    public function modify($data)
    {
        $data = (string) $data;
        if (!empty($data)) {
            ob_start();
            HooksHelper::doAction('footer');
            $footer = ob_get_clean();
            if (!empty($footer) && preg_match('/<\/body[^>]*?>/i', $data, $matches)) {
                $data = str_replace($matches[0], $footer . $matches[0], $data);
            }
        }

        return $data;
    }
}
