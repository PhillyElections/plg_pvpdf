	<?php
/**
 * @version     $Id: pvpdf.php
 * @package     PVotes
 * @subpackage  Content
 * @copyright   Copyright (C) 2015 Philadelphia Elections Commission
 * @license     GNU/GPL, see LICENSE.php
 * @author      Matthew Murphy <matthew.e.murphy@phila.gov>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Let's make sure the translations are loaded
$language = JFactory::getLanguage();
$language->load('plg_pvpdf', JPATH_ADMINISTRATOR, null, null);

jimport('joomla.plugin.plugin');
jimport('kint.kint');

/**
 * Example Content Plugin
 *
 * @package     Joomla
 * @subpackage  Content
 * @since       1.5
 */
class plgContentPvpdf extends JPlugin
{

    /**
     * Constructor
     *
     * @param object $subject The object to observe
     * @param object $params  The object that holds the plugin parameters
     * @since 1.5
     */
    public function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);
    }

    /**
     * Default event
     *
     * Isolate the content and call actual processor
     *
     * @param   object      The article object.  Note $article->text is also available
     * @param   object      The article params
     * @param   int         The 'page' number
     */
    public function onPrepareContent(&$article, &$params, $limitstart)
    {
        global $mainframe;
        if (is_object($article)) {
            return $this->prepPvballotDisplay($article->text);
        }
        return $this->prepPvballotDisplay($article);
    }

    /**
     * Example after display title method
     *
     * Method is called by the view and the results are imploded and displayed in a placeholder
     *
     * @param   object   $article   The article object.  Note $article->text is also available
     * @param   object   $params   The article params
     * @param   int      $limitstart   The 'page' number
     * @return  string
     */
    public function onAfterDisplayTitle(&$article, &$params, $limitstart)
    {
        global $mainframe;

        return '';
    }

    /**
     * Example before display content method
     *
     * Method is called by the view and the results are imploded and displayed in a placeholder
     *
     * @param   object   $article   The article object.  Note $article->text is also available
     * @param   object   $params   The article params
     * @param   int      $limitstart   The 'page' number
     * @return  string
     */
    public function onBeforeDisplayContent(&$article, &$params, $limitstart)
    {
        global $mainframe;

        return '';
    }

    /**
     * Example after display content method
     *
     * Method is called by the view and the results are imploded and displayed in a placeholder
     *
     * @param   object   $article   The article object.  Note $article->text is also available
     * @param   object   $params   The article params
     * @param   int      $limitstart   The 'page' number
     * @return  string
     */
    public function onAfterDisplayContent(&$article, &$params, $limitstart)
    {
        global $mainframe;

        return '';
    }

    /**
     * Example before save content method
     *
     * Method is called right before content is saved into the database.
     * Article object is passed by reference, so any changes will be saved!
     * NOTE:  Returning false will abort the save with an error.
     *  You can set the error by calling $article->setError($message)
     *
     * @param   object   $article   A JTableContent object
     * @param   bool     $isNew   If the content is just about to be created
     * @return  bool        If false, abort the save
     */
    public function onBeforeContentSave(&$article, $isNew)
    {
        global $mainframe;

        return true;
    }

    /**
     * Example after save content method
     * Article is passed by reference, but after the save, so no changes will be saved.
     * Method is called right after the content is saved
     *
     *
     * @param   object   $article   A JTableContent object
     * @param   bool     $isNew   If the content is just about to be created
     * @return  void
     */
    public function onAfterContentSave(&$article, $isNew)
    {
        global $mainframe;

        return true;
    }

    /**
     * Check for a PVBallotdisplay block,
     * skip <script> blocks, and
     * call getPVballotdisplayStrings() as appropriate.
     *
     * @param   string   $text  content
     * @return  bool
     */
    public function prepPvballotDisplay(&$text)
    {
        // Quick, cheap chance to back out.
        if (JString::strpos($text, 'PVPDF') === false) {
            return true;
        }

        $text = explode('<script', $text);
        foreach ($text as $i => $str) {
            if ($i == 0) {
                $this->getPvPdfStrings($text[$i]);
            } else {
                $str_split = explode('</script>', $str);
                foreach ($str_split as $j => $str_split_part) {
                    if (($j % 2) == 1) {
                        $this->getPvPdfStrings($str_split[$i]);
                    }
                }
                $text[$i] = implode('</script>', $str_split);
            }
        }
        $text = implode('<script', $text);

        return true;
    }

    /**
     * Find PVballotDisplay blocks,
     * get display per block.
     *
     * @param   string   $text  content
     * @return  bool
     */
    public function getPvPdfStrings(&$text)
    {
        // Quick, cheap chance to back out.
        if (JString::strpos($text, 'PVPDF') === false) {
            return true;
        }

        $search = "(\[\[PVPDF:.*\]\])";

        while (preg_match($search, $text, $regs, PREG_OFFSET_CAPTURE)) {
            $temp = explode(':', trim(trim($regs[0][0], '[]'), '[]'));
            $file_path = $temp[1];

            if ($field && $value && $content = $this->getContent($file_path)) {
                $text = JString::str_ireplace($regs[0][0], $content, $text);
            }
        }
        return true;
    }

    /**
     * Get ballot data,
     * return ballot display.
     *
     * @param   objectList   $results  ballot data
     * @return  string
     */
    public function getContent(&$file_path)
    {
        return <<<EOT
<object data="/$file_path" type="application/pdf" width="100%" height="100%">
<iframe src="/$file_path" style="border: none;" width="100%" height="100%">
This browser does not support PDFs. Please download the PDF to view it: <a href="/$file_path">Download PDF</a>
</iframe>
</object>
EOT;
    }
}
