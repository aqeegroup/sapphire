<?php
/**
 * Sapphire
 *
 * Licensed under the Massachusetts Institute of Technology
 *
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Lorne Wang < post@lorne.wang >
 * @copyright   Copyright (c) 2014 - 2015 , All rights reserved.
 * @link        http://lorne.wang/projects/sapphire
 * @license     http://lorne.wang/licenses/MIT
 */
namespace Sapphire\Component;

/**
 * Pagination
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Pagination
{
    protected $baseURL = '';
    protected $firstURL = ''; // Alternative URL for the First Page.
    protected $prefix = ''; // A custom prefix added to the path.
    protected $suffix = ''; // A custom suffix added to the path.
    protected $totalNumber = 0; // Total number of items (database results)
    protected $perPage = 10; // Max number of items you want shown per page
    protected $numberLinks = 2; // Number of "digit" links to show before/after the+ currently viewed page
    protected $currentPage = 1; // The current page being viewed

    protected $firstLink = 'First';
    protected $firstTagOpen = '&nbsp;';
    protected $firstTagClose = '&nbsp;';

    protected $nextLink = 'Next';
    protected $nextTagOpen = '&nbsp;';
    protected $nextTagClose = '&nbsp;';

    protected $prevLink = 'Prev';
    protected $prevTagOpen = '&nbsp;';
    protected $prevTagClose = '&nbsp;';

    protected $lastLink = 'Last';
    protected $lastTagOpen = '&nbsp;';
    protected $lastTagClose = '&nbsp;';

    protected $numberTagOpen = '&nbsp;';
    protected $numberTagClose = '&nbsp;';

    protected $currentTagOpen = '<strong>';
    protected $currentTagClose = '</strong>';

    protected $fullTagOpen = '';
    protected $fullTagClose = '';

    protected $pageQueryString = FALSE;
    protected $queryStringSegment = 'page';
    protected $displayPages = TRUE;
    protected $attributes = '';
    protected $linkTypes = [];
    protected $reuseQueryString = FALSE;
    protected $dataPageAttr = 'data-pagination-page';

    protected $linkRewrite = NULL;

    /**
     * Constructor
     *
     * @param array $params
     */
    public function __construct($params = [])
    {
        $attributes = [];

        if (isset($params['attributes']) && is_array($params['attributes']))
        {
            $attributes = $params['attributes'];
            unset($params['attributes']);
        }

        // Deprecated legacy support for the anchor_class option
        if (isset($params['anchor_class']))
        {
            empty($params['anchor_class']) OR $attributes['class'] = $params['anchor_class'];
            unset($params['anchor_class']);
        }

        $this->parseAttributes($attributes);

        if (count($params) > 0)
        {
            foreach ($params as $key => $val)
            {
                if (isset($this->$key))
                {
                    $this->$key = $val;
                }
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set base url
     *
     * @access public
     * @param  string $baseURL
     * @return void
     */
    public function setBaseURL($baseURL)
    {
        $this->baseURL = $baseURL;
    }

    // --------------------------------------------------------------------

    /**
     * Set current page
     *
     * @access public
     * @param  integer $currentPage
     * @return void
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    // --------------------------------------------------------------------

    /**
     * Set total number
     *
     * @access public
     * @param  integer $totalNumber
     * @return void
     */
    public function setTotalNumber($totalNumber)
    {
        $this->totalNumber = $totalNumber;
    }

    // --------------------------------------------------------------------

    /**
     * Set size for per page
     *
     * @access public
     * @param  integer $perPage
     * @return void
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;
    }

    // --------------------------------------------------------------------

    /**
     * Set first link content
     *
     * @access public
     * @param  string $firstLink
     * @return void
     */
    public function setFirstLink($firstLink)
    {
        $this->firstLink = $firstLink;
    }

    // --------------------------------------------------------------------

    /**
     * Set next link content
     *
     * @access public
     * @param  string $nextLink
     * @return void
     */
    public function setNextLink($nextLink)
    {
        $this->nextLink = $nextLink;
    }

    // --------------------------------------------------------------------

    /**
     * Set next open tag
     *
     * @access public
     * @param  string $nextTagOpen
     * @return void
     */
    public function setNextTagOpen($nextTagOpen)
    {
        $this->nextTagOpen = $nextTagOpen;
    }

    // --------------------------------------------------------------------

    /**
     * Set next close tag
     *
     * @access public
     * @param  string $nextTagClose
     * @return void
     */
    public function setNextTagClose($nextTagClose)
    {
        $this->nextTagClose = $nextTagClose;
    }

    // --------------------------------------------------------------------

    /**
     * Set prev link content
     *
     * @access public
     * @param  string $prevLink
     * @return void
     */
    public function setPrevLink($prevLink)
    {
        $this->prevLink = $prevLink;
    }

    // --------------------------------------------------------------------

    /**
     * Set prev open tag
     *
     * @access public
     * @param  string $prevTagOpen
     * @return void
     */
    public function setPrevTagOpen($prevTagOpen)
    {
        $this->prevTagOpen = $prevTagOpen;
    }

    // --------------------------------------------------------------------

    /**
     * Set prev close tag
     *
     * @access public
     * @param  string $prevTagClose
     * @return void
     */
    public function setPrevTagClose($prevTagClose)
    {
        $this->prevTagClose = $prevTagClose;
    }

    // --------------------------------------------------------------------

    /**
     * Set last link content
     *
     * @access public
     * @param  string $lastLink
     * @return void
     */
    public function setLastLink($lastLink)
    {
        $this->lastLink = $lastLink;
    }

    // --------------------------------------------------------------------

    /**
     * Set number open tag
     *
     * @access public
     * @param  string $numberTagOpen
     * @return void
     */
    public function setNumberTagOpen($numberTagOpen)
    {
        $this->numberTagOpen = $numberTagOpen;
    }

    // --------------------------------------------------------------------

    /**
     * Set number close tag
     *
     * @access public
     * @param  string $numberTagClose
     * @return void
     */
    public function setNumberTagClose($numberTagClose)
    {
        $this->numberTagClose = $numberTagClose;
    }

    // --------------------------------------------------------------------

    /**
     * Set current open tag
     *
     * @access public
     * @param  string $currentTagOpen
     * @return void
     */
    public function setCurrentTagOpen($currentTagOpen)
    {
        $this->currentTagOpen = $currentTagOpen;
    }

    // --------------------------------------------------------------------

    /**
     * Set current close tag
     *
     * @access public
     * @param  string $currentTagClose
     * @return void
     */
    public function setCurrentTagClose($currentTagClose)
    {
        $this->currentTagClose = $currentTagClose;
    }

    // --------------------------------------------------------------------

    /**
     * Set number count half of links
     *
     * @access public
     * @param  string $numberLinks
     * @return void
     */
    public function setNumberLinks($numberLinks)
    {
        $this->numberLinks = $numberLinks;
    }

    // --------------------------------------------------------------------

    /**
     * Set link url rewrite closure
     *
     * @access public
     * @param  \Closure $closure
     * @return void
     */
    public function setLinkRewrite(\Closure $closure)
    {
        $this->linkRewrite = $closure;
    }

    // --------------------------------------------------------------------

    /**
     * Generate the pagination links
     *
     * @access public
     * @throws
     * @return string
     */
    public function show()
    {
        // If our item count or per-page total is zero there is no need to continue.
        if ($this->totalNumber === 0 OR $this->perPage === 0)
        {
            return '';
        }

        // Calculate the total number of pages
        $numberPages = (int) ceil($this->totalNumber / $this->perPage);

        // Is there only one page? Hm... nothing more to do here then.
        if ($numberPages === 1)
        {
            return '';
        }

        // Set the base page index for starting page number
        $defaultPage = 1;
        $queryGet = $_GET;

        // Determine the current page number.
        if (isset($queryGet[$this->queryStringSegment]) && $queryGet[$this->queryStringSegment] != $defaultPage)
        {

            $this->currentPage = (int) $queryGet[$this->queryStringSegment];
        }

        // Set current page to 1 if it's not valid or if using page numbers instead of offset
        if ( ! is_numeric($this->currentPage) OR $this->currentPage === 0)
        {
            $this->currentPage = $defaultPage;
        }

        $this->numberLinks = (int) $this->numberLinks;

        if ($this->numberLinks < 1)
        {
            throw new \Exception('Your number of links must be a positive number.');
        }

        // Is the page number beyond the result range?
        // If so we show the last page
        if ($this->currentPage > $numberPages)
        {
            $this->currentPage = $numberPages;
        }

        $uriPageNumber = $this->currentPage;

        // Calculate the start and end numbers. These determine 
        // which number to start and end the digit links with
        $start = (($this->currentPage - $this->numberLinks) > 0) ? $this->currentPage - ($this->numberLinks - 1) : 1;
        $end = (($this->currentPage + $this->numberLinks) < $numberPages) ? $this->currentPage + $this->numberLinks : $numberPages;

        // Is pagination being used over GET or POST? If get, add a per_page query
        // string. If post, add a trailing slash to the base URL if needed
        $this->baseURL = trim($this->baseURL);
        unset($queryGet[$this->queryStringSegment]);

        $queryMark = substr($this->baseURL, -1) == '?' ? '' : '?';
        $queryString = http_build_query($queryGet);

        $buildQuery = count($queryGet) > 0 ? "{$queryMark}{$queryString}&" : $queryMark;
        $this->baseURL = rtrim($this->baseURL) . $buildQuery . $this->queryStringSegment . '=';

        // And here we go...
        $output = '';

        // Render the "First" link
        if ($this->firstLink !== FALSE && $this->currentPage > ($this->numberLinks + 1))
        {
            $firstURL = ($this->firstURL === '') ? $this->baseURL : $this->firstURL;

            // Take the general parameters, and squeeze this pagination-page attr in there for JS fw's
            $attributes = sprintf('%s %s="%d"', $this->attributes, $this->dataPageAttr, 1);

            $output .= $this->firstTagOpen . '<a href="' . $this->rebuildUrl($firstURL) . '"' . $attributes . $this->attributeRel('start') . '>'
                . $this->firstLink . '</a>' . $this->firstTagClose;
        }

        // Render the "previous" link
        if ($this->prevLink !== FALSE && $this->currentPage !== 1)
        {
            $i = $uriPageNumber - 1;

            // Take the general parameters, and squeeze this pagination-page attr in there for JS fw's
            $attributes = sprintf('%s %s="%d"', $this->attributes, $this->dataPageAttr, (int) $i);

            if ($i === $defaultPage && $this->firstURL !== '')
            {
                $output .= $this->prevTagOpen . '<a href="' . $this->rebuildUrl($this->firstURL) . '"' . $attributes . $this->attributeRel('prev') . '>'
                    . $this->prevLink . '</a>' . $this->prevTagClose;
            }
            else
            {
                $append = ($i === $defaultPage) ? $queryString : $this->prefix . $i . $this->suffix;
                $output .= $this->prevTagOpen . '<a href="' . $this->rebuildUrl($this->baseURL . $append) . '"' . $attributes . $this->attributeRel('prev') . '>'
                    . $this->prevLink . '</a>' . $this->prevTagClose;
            }
        }

        // Render the pages
        if ($this->displayPages !== FALSE)
        {
            // Write the digit links
            for ($loop = $start - 1; $loop <= $end; $loop++)
            {
                // Take the general parameters, and squeeze this pagination-page attr in there for JS fw's
                $attributes = sprintf('%s %s="%d"', $this->attributes, $this->dataPageAttr, (int) $loop);

                if ($loop >= $defaultPage)
                {
                    if ($this->currentPage === $loop)
                    {
                        $output .= $this->currentTagOpen . $loop . $this->currentTagClose; // Current page
                    }
                    else
                    {
                        $n = ($loop === $defaultPage) ? '' : $loop;

                        if ($n === '' && ! empty($this->firstURL))
                        {
                            $output .= $this->numberTagOpen . '<a href="' . $this->rebuildUrl($this->firstURL) . '"' . $attributes . $this->attributeRel('start') . '>'
                                . $loop . '</a>' . $this->numberTagClose;
                        }
                        else
                        {
                            $append = ($n === '') ? $defaultPage : $this->prefix . $n . $this->suffix;
                            $output .= $this->numberTagOpen . '<a href="' . $this->rebuildUrl($this->baseURL . $append) . '"' . $attributes . $this->attributeRel('start') . '>'
                                . $loop . '</a>' . $this->numberTagClose;
                        }
                    }
                }
            }
        }

        // Render the "next" link
        if ($this->nextLink !== FALSE && $this->currentPage < $numberPages)
        {
            $i = $this->currentPage + 1;

            // Take the general parameters, and squeeze this pagination-page attr in there for JS fw's
            $attributes = sprintf('%s %s="%d"', $this->attributes, $this->dataPageAttr, (int) $i);

            $output .= $this->nextTagOpen . '<a href="' . $this->rebuildUrl($this->baseURL . $this->prefix . $i . $this->suffix) . '"' . $attributes
                . $this->attributeRel('next') . '>' . $this->nextLink . '</a>' . $this->nextTagClose;
        }

        // Render the "Last" link
        if ($this->lastLink !== FALSE && ($this->currentPage + $this->numberLinks) < $numberPages)
        {
            // Take the general parameters, and squeeze this pagination-page attr in there for JS fw's
            $attributes = sprintf('%s %s="%d"', $this->attributes, $this->dataPageAttr, (int) $numberPages);

            $output .= $this->lastTagOpen . '<a href="' . $this->rebuildUrl($this->baseURL . $this->prefix . $numberPages . $this->suffix) . '"' . $attributes . '>'
                . $this->lastLink . '</a>' . $this->lastTagClose;
        }

        // Kill double slashes. Note: Sometimes we can end up with a double slash
        // in the penultimate link so we'll kill all double slashes.
        $output = preg_replace('#([^:])//+#', '\\1/', $output);

        // Add the wrapper HTML if exists
        return $this->fullTagOpen . $output . $this->fullTagClose;
    }

    // --------------------------------------------------------------------

    /**
     * Rebuild url
     *
     * @param  string $url
     * @return string
     */
    protected function rebuildUrl($url)
    {
        if (is_callable($this->linkRewrite))
        {
            $method = $this->linkRewrite;

            return $method($url);
        }

        return $url;
    }

    // --------------------------------------------------------------------

    /**
     * Parse attributes
     *
     * @param  array $attributes
     * @return void
     */
    protected function parseAttributes($attributes)
    {
        isset($attributes['rel']) OR $attributes['rel'] = TRUE;
        $this->linkTypes = ($attributes['rel'])
            ? ['start' => 'start', 'prev' => 'prev', 'next' => 'next']
            : [];
        unset($attributes['rel']);

        $this->attributes = '';
        foreach ($attributes as $key => $value)
        {
            $this->attributes .= ' ' . $key . '="' . $value . '"';
        }
    }

    // --------------------------------------------------------------------

    /**
     * Add "rel" attribute
     *
     * @link   http://www.w3.org/TR/html5/links.html#linkTypes
     * @param  string $type
     * @return string
     */
    protected function attributeRel($type)
    {
        if (isset($this->linkTypes[$type]))
        {
            unset($this->linkTypes[$type]);

            return ' rel="' . $type . '"';
        }

        return '';
    }
}
