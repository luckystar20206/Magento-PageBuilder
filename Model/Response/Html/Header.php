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

class Header implements ModifierInterface
{
    /**
     * Add JS/CSS to header of specific page
     *
     * @param $data
     * @return string
     */
    public function modify($data)
    {
        $data = (string) $data;
        if (!empty($data)) {
            $areaCode = StateHelper::getAreaCode();
            ob_start();
            HooksHelper::doAction('header');
            $header = ob_get_clean();
            if (!empty($header)) {
                $data = str_replace('</head>', $header . '</head>', $data);
            }
        }

        return $data;
    }
}