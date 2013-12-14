<?PHP

class paginate {
    /**
     * A simple pagination class.
     * @author Dimitri Topaloglou <dtopaloglou@gmail.com>
     * @copyright (c) 2013, Dimitri Topaloglou
     */

    /**
     *
     * @var int Maximum results per page 
     */
    private $_per_page = 50;

    /**
     *
     * @var int Current page number 
     */
    private $page = 1;

    /**
     *
     * @var type Number of links to be displayed
     */
    private $_ranger = 8;

    /**
     *
     * @var string Location of the page being paginated.
     */
    private $_returnURL;

    /**
     *
     * @var string  URL query string that will be displayed in the links 
     */
    private $_linkURL;

    /**
     *
     * @var string  Raw SQL statement. No to include LIMIT 
     */
    private $_sql;

    /**
     *
     * @var int Number of results based on query 
     */
    private $_sql_rows;

    /**
     *
     * @var string  Link title 
     */
    private $_Title = 'Page ';

    /**
     *
     * @var string Link title
     */
    private $_Forward = 'Forward by ';

    /**
     *
     * @var string  Link title 
     */
    private $_Previous = 'Back by ';

    /**
     *
     * @var string Link title
     */
    private $_Last = 'Last page ';

    /**
     *
     * @var string Link title
     */
    private $_First = 'First page ';

    /**
     *
     * @var string Link title
     */
    private $_Next = 'Next ';

    /**
     *
     * @var string Link title
     */
    private $_Back = 'Back ';

    /**
     *
     * @var string CSS class name give to <div> containing links
     * @see setClasses()
     */
    private $_divClass = 'pagination';

    /**
     *
     * @var string CSS class name given to <span> containing active link
     * @see setClasses()
     */
    private $_currentClass = 'current';

    /**
     *
     * @var string CSS class name given to <span> containing non-active links 
     * @see setClasses()
     */
    private $_disableClass = 'disabled';

    /**
     *
     * @var PDO PDO instance 
     */
    protected $_PDO;

    /**
     *
     * @var array  PDO parameters required in PDOStatement::execute.
     * @link http://www.php.net/manual/en/pdostatement.execute.php
     */
    protected $_params;

    /**
     *
     * @var array Fetches rows from results associated with PDOStatement Object.
     * @link http://www.php.net/manual/en/pdostatement.fetch.php
     */
    private $_t;

    /**
     * 
     * @param PDO PDO instance
     * @link http://www.php.net/manual/en/class.pdo.php
     */
    public function __construct($PDO) {
        if ($PDO instanceof PDO) {
            $this->_PDO = $PDO;
        }
    }

    /**
     * Queries SQL statement with associated parameters and sets maximum results to be displayed per page.
     * @param string    $sql    SQL statement.
     * @param array     $parameters     Binds parameter values.
     * @param int       $results       Sets number of results to be displayed on page.
     */
    public function query($sql, $parameters = NULL, $results = NULL) {

        if (is_numeric($results) && $results > 0) {
            $this->_per_page = $results;
        }
        $this->_sql = $sql;
        $this->_params = $parameters;
        $this->init();
    }

    /**
     * This URL defines the query string that will be appended after the return URL.
     * @param string $url
     */
    public function setUrl($url) {
        $this->_linkURL = $url;
    }

    /**
     * A return URL is a URL that is to be returned within links along with page numbers.
     * 
     * A valid return URL should look something like this: ?var=&var2=&var3
     *
     * The query string is left up to the developer to build and parse in the class.
     * 
     * @param string $url
     */
    public function setReturnUrl($url) {
        $this->_returnURL = $url;
    }

    /**
     * Initializes query to retrieve and retrieves number of results.
     */
    private function init() {
        $query = $this->_PDO->prepare($this->_sql);
        if ($query->execute($this->_params)) {
            $this->_sql_rows = $query->rowCount();
            $this->pageQuery();
        }
    }

    /**
     * 
     * @return array Returns query parameters
     * @see query()
     */
    public function displayParams() {
        return $this->_params;
    }

    /**
     * Returns raw SQL statement.
     * @return string 
     */
    final public function displaySQL() {
        return $this->buildQuery();
    }

    /**
     * Retrieve results per page.
     * @return integer 
     */
    public function getPerPage() {
        return $this->_per_page;
    }

    /**
     * Set customized language titles for links.
     * @param array $data 
     */
    public function setLanguageTITLES($data = array()) {
        $this->_Title = $data['title'];
        $this->_Forward = $data['forward'];
        $this->_Previous = $data['previous'];
        $this->_Last = $data['last'];
        $this->_First = $data['first'];
        $this->_Next = $data['next'];
        $this->_Back = $data['back'];
    }

    /**
     * Sets CSS class names for the <div> and <span> content.
     * @param array $css
     */
    public function setClasses($css = array()) {
        $this->_divClass = $css['paginate'];
        $this->_currentClass = $css['current'];
        $this->_disableClass = $css['disabled'];
    }

    /**
     * Sets the number of links to be displayed.
     * @param integer $total
     */
    public function setAdjacentLinks($total) {
        if (is_numeric($total) && $total > 0) {
            $this->_ranger = $total;
        }
    }

    /**
     * @return integer Returns the number of links that will be displayed.
     */
    public function getAdjacentLinks() {
        return $this->_ranger;
    }

    /**
     * Sets the LIMIT in the SQL statement.
     * @return string
     */
    final protected function setLimit() {
        return ' LIMIT ' . $this->getStartPage() . ', ' . $this->getPerPage();
    }

    /**
     * Builds the SQL statement and appends the LIMIT which is based on the number of results per page.
     * @see setLimit()
     * @return string 
     */
    protected function buildQuery() {
        $sql = $this->_sql;
        $limit = $this->setLimit();
        return $sql . $limit;
    }

    /**
     * Returns the number of results of the query with limit statement.
     * @see results()
     * @return integer 
     */
    protected function currentRows() {
        return $this->_t->rowCount();
    }

    /**
     * Returns results within PDO fetch() method. This method is to be called outside in order to display results. 
     * @return array Passes PDOStatement::fetch
     */
    final public function fetch() {
        return $this->_t->fetch();
    }

    /**
     * @return integer Retrieves the current page number. 
     */
    final public function getPage() {
        if ($this->page <= 0) {
            return 1;
        } elseif ($this->page >= $this->getTotalPages()) {
            return $this->getTotalPages();
        } else {
            return $this->page;
        }
    }

    /**
     * Set the current page. This is usually set by the superglobal $_GET variable.
     * The superglobal $_GET is not necessary, though the class needs to know which page is currently displayed in order to display appropriate results.
     * @param int $page Page number.
     */
    public function setCurrentPage($page) {
        if (is_numeric($page)) {
            $this->page = $page;
        }
    }

    /**
     * Returns the starting page based on the current page.
     * @return integer Returns start page
     */
    private function getStartPage() {
        if ((($this->getPage() - 1) * $this->getPerPage()) <= 0) {
            return 0;
        } else {
            return (($this->getPage() - 1) * $this->getPerPage());
        }
    }

    /**
     * @return integer Retrieve number of pages.
     */
    public function getTotalPages() {
        return ceil($this->results() / $this->getPerPage());
    }

    private function leftLinks() {
        if (($this->getPage() - $this->getAdjacentLinks()) >= 1) {  // How many adjacent pages to show on left.
            return $this->getAdjacentLinks();
        } else {
            return $this->getPage() - 1;
        }
    }

    private function rightLinks() {
        if (($this->getPage() + $this->getAdjacentLinks()) <= $this->getTotalPages()) { // How many adjacent pages to show towards right.
            return $this->getAdjacentLinks();
        } else {
            return $this->getTotalPages() - $this->getPage();
        }
    }

    /**
     * @return int Retrieves next page number.
     */
    final protected function nextPage() {
        return $this->getPage() + 1;
    }

    /**
     * @return int Retrieves previous page number.
     */
    final protected function previousPage() {
        return $this->getPage() - 1;
    }

    /**
     * Retrieves number of results of the query. This excludes any LIMIT statement.
     * @return integer Returns number of results.
     */
    public function results() {
        return $this->_sql_rows;
    }

    /**
     * Contains an array for the amount of pages to be skipped after a certain page number threshold. The higher the amount of page results, the larger the skip.
     * <p>Example:
     * <br>
     * After 100 pages, the skipper link will jump forward 10 pages. 
     * At 1000 pages, the skipper link will jump forward 75 pages instead of 10.
     * </p>
     * @return array 
     */
    private function levels() {
        $array = array(100 => 10,
            1000 => 75,
            5000 => 250,
            10000 => 500
        );
        return $array;
    }

    /**
     * Build skipper based on level.
     * @see levels()
     */
    private function skipper() {
        $levels = $this->levels();
        foreach ($levels as $i => $level) {
            if ($this->getPage() <= $i) {
                return $levels[$i];
            }
        }
    }

    /**
     * Returns the URL that will be displayed in every link. The page number will be appended automatically.
     * <p>
     *  <ul>
     *      <li>If the URL is not set, the page variable will be attached to the return URL.</li>
     *      <li>If the URL is set, the URL will be attached the return URL along with the page variable.</li>
     *      <li>If the final output is not a valid query string, the superglobal $_SERVER['PHP_SELF'] will be assumed instead.</li>
     *  </ul>
     * </p>
     * @return string URL query string
     */
    protected function returnURL() {
        $return = $this->_returnURL;
        $link_url = $this->_linkURL;

        if ($link_url == "" || empty($link_url)) { // if URL is empty, attach it to return URL and append page var
            $url = $return . '?page';
        } else {
            $url = $return . "$link_url&page"; // if URL is not emppty, attach it to return URL and page var
        }

        $combine = $return . $url;


        if (!filter_var($combine, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED)) {
            return $combine;
        } else {
            return $_SERVER['PHP_SELF'] . '?page';
        }
    }

    /**
     * Displays results based on page number and the total number of results.
     * 
     * Example output:
     * 
     * 1 - 50 / 5,000 r(esults 1 thru 50 out of 5000)
     * 
     * @return string Page results per total results.
     */
    public function resultOffset() {
        $out = "";
        $ouput = "<span>%s</span> - <span>%s</span>";
        $final = " / <span>%s</span>";

        $page = $this->getPage();   // current page
        $results = $this->results();   // nuber of results
        $totalpages = $this->getTotalPages(); // total pages

        if ($results > 0) {
            switch ($page) {
                case $page == 1 && $results > $this->getPerPage():
                    $out .= sprintf($ouput, "1", (0 + $this->getPerPage()));
                    break;
                case $page > 1 && $page < $totalpages:
                    $out .= sprintf($ouput, ((($page * $this->getPerPage()) - $this->getPerPage()) + 1), ($page * $this->getPerPage()));
                    break;
                case $page == $totalpages:
                    $out .= sprintf($ouput, ((($page * $this->getPerPage()) - $this->getPerPage()) + 1), $results);
                    break;
                case $page == 1 && $results < $this->getPerPage():
                    $out .= sprintf('1', $results);
                    break;
                default:
                    break;
            }
        } else {
            $out .= sprintf('0', $results);
        }
        return $out . sprintf($final, number_format($results));
    }

    /**
     * @return string Displays links.
     */
    public function displayLinks() {
        $page = $this->getPage();   // current page
        $totalpages = $this->getTotalPages(); // total pages
        $nextpage = $this->nextPage();  // next page
        $previouspage = $this->previousPage(); // previous page

        $OUT = '';
        $OUT .= "<div class=\"" . $this->_divClass . "\">";
        $OUT .= ($page > 2 && $page <= $totalpages ? " <a paginateRef=\"1\" href=\"" . $this->returnURL() . "=1\" title=\"" . $this->_First . "\">1</a>" : "");
        $OUT .= (($page - 1) > $this->skipper() ? " <a  paginateRef=\"" . $this->skipper() . "\"  href=\"" . $this->returnURL() . "=" . ($page - $this->skipper()) . "\" title=\"" . $this->_Previous . "" . $this->skipper() . "\"> ... </a>" : "");

        $OUT .= ($page > 2 && $page <= $totalpages ? " <a  paginateRef=\"" . $previouspage . "\"  href=\"" . $this->returnURL() . "=" . $previouspage . "\" title=\"" . $this->_Title . " " . $previouspage . "\">&laquo; " . $this->_Back . "</a>" : "<span class=\"" . $this->_disableClass . "\">&laquo; " . $this->_Back . "</span>");

        for ($x = ( $page - $this->leftLinks() ); $x <= ( $page + $this->rightLinks() ); $x++) {  /// Create the links depending on range.
            if ($x == $page) {
                $OUT .= "<span class=\"" . $this->_currentClass . "\">" . $x . "</span>";
            } else {
                $OUT .= " <a paginateRef=\"" . $x . "\" href=\"" . $this->returnURL() . "=" . $x . "\" title=\"" . $this->_Title . " " . $x . "\">" . $x . "</a> ";
            }
        }
        $OUT .= ($page >= 1 && $page < ($totalpages - 1) ? " <a  paginateRef=\"" . $nextpage . "\" href=\"" . $this->returnURL() . "=" . $nextpage . "\" title=\"" . $this->_Title . " " . $nextpage . "\">" . $this->_Next . " &raquo;</a>" : "<span class=\"" . $this->_disableClass . "\">" . $this->_Next . "  &raquo;</span>");

        $OUT .= (($totalpages - $page) > $this->skipper() ? " <a paginateRef=\"" . $this->skipper() . "\" href=\"" . $this->returnURL() . "=" . ($page + $this->skipper()) . "\" title=\"" . $this->_Forward . " " . $this->skipper() . "\"> ... </a>" : "");
        $OUT .= (($page >= 1) && $page < ($totalpages - 1) ? " <a paginateRef=\"" . $totalpages . "\" href=\"" . $this->returnURL() . "=" . $totalpages . "\"  title=\"" . $this->_Last . "\">" . $totalpages . "</a>" : "");
        $OUT .= "</div>";

        return $OUT;
    }

    /**
     * Queries database with LIMIT statement. This method must be called in order to paginate results.
     */
    private function pageQuery() {
        $this->_t = $this->_PDO->prepare($this->buildQuery());
        $this->_t->execute($this->_params);
    }

}

?>