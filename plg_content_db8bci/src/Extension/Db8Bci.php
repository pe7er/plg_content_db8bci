<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.db8bci
 *
 * @author      Peter Martin <joomla@db8.nl>
 * @copyright   Copyright (C) 2025 Peter Martin. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://db8.nl
 */

namespace Db8\Plugin\Content\Db8Bci\Extension;

use DOMDocument;
use DOMElement;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Db8 BCI - Backend Content Images
 *
 * @since  1.0.0
 */
final class Db8Bci extends CMSPlugin
{
    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

    /** @var CMSApplication */
    protected $app;

    /** @var bool Load the language file on instantiation */
    protected $autoloadLanguage = true;

    /** @var array Stores plugin parameters */
    private array $pluginParams;

    /**
     * @param \Joomla\Event\DispatcherInterface $dispatcher
     * @param array $config
     * @throws \Exception
     */
    public function __construct(DispatcherInterface $dispatcher, array $config)
    {
        parent::__construct($dispatcher, $config);
        $this->app = Factory::getApplication();
        $this->pluginParams = $this->params->toArray();
    }

    /**
     * Listener for the `onAfterRender` event
     *
     * @return  void
     * @throws \Exception
     * @since   1.0.0
     */
    public function onAfterRender(): void
    {
        $params = $this->pluginParams;

        /**
         * Article Intro Images in back-end
         */
        if (
            $params['enableBackendArticleImages'] &&
            $this->app->isClient('administrator') &&
            $this->isView('com_content', 'articles')
        ) {
            $this->renderBackendImages('articleList', 'getArticleIntroImage',
                Text::_('PLG_CONTENT_DB8BCI_BACKEND_ARTICLE_IMAGE'));
        }

        /**
         * Category Images in back-end
         */
        if (
            $params['enableBackendCategoryImages'] &&
            $this->app->isClient('administrator') &&
            $this->isView('com_categories', 'categories', 'com_content')
        ) {
            $this->renderBackendImages('categoryList', 'getCategoryImage',
                Text::_('PLG_CONTENT_DB8BCI_BACKEND_CATEGORY_IMAGE')
            );
        }
    }

    /**
     * @param string $tableId
     * @param string $imageMethod
     * @param string $headerText
     * @return void
     * @throws \Exception
     */
    private function renderBackendImages(string $tableId, string $imageMethod, string $headerText): void
    {
        $buffer = $this->app->getBody();
        libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'utf-8');
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $buffer, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $table = $dom->getElementById($tableId);
        if (!$table) {
            return;
        }

        $this->addTableHeader($table, $dom, $headerText);
        $this->addImagesToTable($table, $dom, [$this, $imageMethod]);

        $this->app->setBody($dom->saveHTML());
    }

    /**
     * @param \DOMElement $table
     * @param \DOMDocument $dom
     * @param string $headerText
     * @return void
     * @throws \DOMException
     */
    private function addTableHeader(DOMElement $table, DOMDocument $dom, string $headerText): void
    {
        $thead = $table->getElementsByTagName('thead')->item(0);
        if (!$thead) {
            return;
        }

        $headerRow = $thead->getElementsByTagName('tr')->item(0);
        if (!$headerRow) {
            return;
        }

        $th = $dom->createElement('th', Text::_($headerText));
        $th->setAttribute('scope', 'col');
        $th->setAttribute('class', 'w-1 text-center');
        $headerRow->appendChild($th);
    }

    /**
     * @param \DOMElement $table
     * @param \DOMDocument $dom
     * @param callable $getImageCallback
     * @return void
     * @throws \DOMException
     */
    private function addImagesToTable(DOMElement $table, DOMDocument $dom, callable $getImageCallback): void
    {
        $params = $this->pluginParams;
        $tbody = $table->getElementsByTagName('tbody')->item(0);

        if (!$tbody) {
            return;
        }

        foreach ($tbody->getElementsByTagName('tr') as $row) {
            $id = $this->extractIdFromRow($row);
            if (!$id) {
                continue;
            }

            $image = $getImageCallback($id);
            if ($image) {
                $this->addImageCell($row, $dom, $image, $params);
            }
        }
    }

    /**
     * @param \DOMElement $row
     * @param \DOMDocument $dom
     * @param string $image
     * @param array $params
     * @return void
     * @throws \DOMException
     */
    private function addImageCell(DOMElement $row, DOMDocument $dom, string $image, array $params): void
    {
        $imageCell = $dom->createElement('td');
        $filename = JPATH_ROOT . '/' . strtok($image, '#');

        if (file_exists($filename)) {
            [$width, $height] = getimagesize($filename);

            if ($params['showArticleImagePlaceholder'] ?? false) {
                $svg = $this->generatePlaceholderSvg($width, $height);
                $fragment = $dom->createDocumentFragment();
                $fragment->appendXML($svg);
                $imageCell->appendChild($fragment);
            } else {
                $img = $dom->createElement('img');
                $img->setAttribute('src', Uri::root() . $image);
                $img->setAttribute('style',
                    'max-width: ' . $params['articleImagesMaxWidth'] . 'px; '
                    . 'max-height: ' . $params['articleImagesMaxHeight'] . 'px;');
                $imageCell->appendChild($img);
            }

            $row->appendChild($imageCell);
        }
    }

    /**
     * @param int $width
     * @param int $height
     * @return string
     */
    private function generatePlaceholderSvg(int $width, int $height): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="75" height="50" viewBox="0 0 75 50">
    <rect fill="#ddd" width="75" height="50"/>
    <text fill="rgba(0,0,0,0.5)" font-family="sans-serif" font-size="12" dy="10.5"
          font-weight="bold" x="50%" y="40%" text-anchor="middle">
        $width x $height
    </text>
</svg>
SVG;
    }

    /**
     * Extract the ID from a specific row by targeting the last <td>.
     *
     * @param \DOMElement $row The table row element.
     * @return int|null The extracted article ID, or null if not found.
     */
    private function extractIdFromRow(DOMElement $row): ?int
    {
        // Get all <td> elements in the row
        $cells = $row->getElementsByTagName('td');

        // Ensure the row has at least one <td>
        if ($cells->length === 0) {
            return null;
        }

        // Target the last <td> before the "Image" (last column)
        $idCell = $cells->item($cells->length - 1); // Second to last cell
        if (!$idCell) {
            return null;
        }

        $idText = trim($idCell->textContent);

        // Validate and return the ID if it's numeric
        return is_numeric($idText) ? (int)$idText : null;
    }

    /**
     * @param string $option
     * @param string $view
     * @param string|null $extension
     * @return bool
     */
    private function isView(string $option, string $view, ?string $extension = null): bool
    {
        return $this->app->input->get('option') === $option
            && $this->app->input->get('view') === $view
            && (!$extension || $this->app->input->get('extension') === $extension);
    }

    /**
     * @param int $articleId
     * @return string|null
     */
    private function getArticleIntroImage(int $articleId): ?string
    {
        return $this->fetchImageFromDatabase('#__content', 'images', 'image_intro', $articleId);
    }

    /**
     * @param int $categoryId
     * @return string|null
     */
    private function getCategoryImage(int $categoryId): ?string
    {
        return $this->fetchImageFromDatabase('#__categories', 'params', 'image', $categoryId);
    }

    /**
     * @param string $table
     * @param string $column
     * @param string $key
     * @param int $id
     * @return string|null
     */
    private function fetchImageFromDatabase(string $table, string $column, string $key, int $id): ?string
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName($column))
            ->from($db->quoteName($table))
            ->where($db->quoteName('id') . ' = ' . $id);
        $db->setQuery($query);

        $result = $db->loadResult();
        $result = $result ? json_decode($result, true) : [];

        return $result[$key] ?? null;
    }
}
