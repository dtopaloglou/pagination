Pagination Class
========
<strong>Screenshot</strong>

![ScreenShot](https://raw.github.com/dtopaloglou/pagination/gh-pages/screenshots/pages.png)


<strong>PDO Connection</strong>
<br>
Pagination queries the database with the PDO object. Therefore, you need to connect to the database via PDO.
```php
$dsn = 'mysql:host=MYHOST;dbname=MYDB;port=3306;connect_timeout=15';            
$user = 'john';             
$password = 'smith';

$PDO = new PDO($dsn, $user, $password);
```
Once you've created your connection, it needs to be implemented. 
```php 
$pagination = new paginate($PDO);
```
And that's it! But we don't want to do that just yet. We need to build a query before doing anything else.

<strong>Building Queries</strong>
<br>
One thing to be noted, is that all SQL queries must be formed in such a way that they accept named placeholders. Here's an example of an SQL statement with a name placeholder:

```php

 $sql = "SELECT phone FROM customers WHERE customer_name=:name";
```

Since the idea is to have named placeholders to all the legwork, an array must be created in order to hold values of your queries. The tricky part is to be able to append to your parameters into an array depending on what you've created as a search as a search engine.


```php
$parameters = array();

if(isset($_GET['phone']))
{
    $sql = "SELECT address FROM customers WHERE phone=:phone";
    $parameters[':phone'] = '555-2401';
}
if(isset($_GET['phone']) && isset($_GET['name']))
{
    $sql = "SELECT address FROM customers WHERE phone=:phone AND customer_name:name ";
    $parameters[':phone'] = '555-2401';
    $parameters[':name'] = 'Kookies Inc.';
}

```


The first 'if' statement checks the address depending on the phone number that's given. Notice a <code>:phone</code> placeholder has been placed (the actual name is arbitrary). The second statement checks the address depending on the phone number and the customer's name, and hence there needs to be two placeholders and two parameters matching them. Please note the name of the placeholder must match the one found in the array.

The next thing to do is to include your SQL statement along with your parameters in the pagination.

```php
$parameters = array();

if(isset($_GET['phone']))
{
    $sql = "SELECT address FROM customers WHERE phone=:phone";
    $parameters[':phone'] = '555-2401';
}
if(isset($_GET['phone']) && isset($_GET['name']))
{
    $sql = "SELECT address FROM customers WHERE phone=:phone AND customer_name:name ";
    $parameters[':phone'] = '555-2401';
    $parameters[':name'] = 'Kookies Inc.';
}

$pagination = new paginate($PDO); 
$pagination->query($sql, $parameters);
```

If you don't feel like using placeholders, you may use variables in your SQL statement and neglect adding your  ``` $parameters ``` array: it will execute normally (not recommended).

<strong>Displaying results</strong>

At this point, your SQL statement has been queried, but we haven't displayed anything. In order for results to be displayed, we need to know the page we're on and how many results we wish to display. The default number of results is 50 per page, but this can be changed. 

For simplistic purposes, the page number is being retrieved via a ```$_GET ```variable. Although how you tell the class what page number you're on is up to you. 

```php
$pagination->setCurrentPage($_GET['page']);
```

The idea of the pagination class to is to display links and results according to the page number and the number of results per page. To display your links, simply add <code>$pagination->displayLinks();</code> anywhere on the page you wish to have your links displayed. However, we're still missing the results. To display your results, you may simply use a <code>while</code> loop to fetch the results from the query and place it where needed.

```php
while($data = $pagination->fetch())
{
    echo $data['address'].'\n';
}

```
At this point, your results will be displayed.

<strong>What about the links?</strong>

You may have been wondering where the links point to when clicked. By default, the links point to your current page and append a <code>page</code> variable. In other words, ```$_SERVER['PHP_SELF']``` is the current page you're on a a simple <code> ?page= </code> will be appended to it to keep track of the page number. If ```php $_SERVER['PHP_SELF'] ``` is NOT the page you're on, then you may provide an alternate page instead:

```php
$pagination->setReturnUrl("blah/mypage.php");
```

The other question you might be askign yourself is, what about all the ```$_GET ``` variables I might already have? Well this part I left it up to the developer. I won't deal with what you have as data contained in your  ```php $_GET ```. However, the solution to this is to provide the query string in the class, and it'll re-assemble it for you and it will show up in the links.

Here's an example of what is meant: 
```php 
$url = "?phone=$_GET[phone]&name=$_GET[name] 
```

. Please note that you must NOT include the ```$_GET[page] ``` variable as that will be automatically appended to the links during build.

Once you've set up your variables, make sure it's valid or it will not be added. 

```php

$pagination->setUrl($url);
```

The output of this result should look like this: ``` <a href="blah/mypage.php?phone=$_GET[phone]&name=$_GET[name]"> ``` and is what you should see as your link. Keep in mind though that the <code>page</code> variable will also be included.

##Useful stuff

<strong>Setting a language</strong>
The default language is English (link titles), but this can be changed.
```php
$language['title']      = 'Page ';
$language['forward']    = 'Forward by ';
$language['previous']   = 'Previous by ';
$language['last']       = 'Last page ';
$language['first']      = 'First page ';
$language['next']       = 'Next ';
$language['back']       = 'Back ';

$pagination->setLanguageTITLES($language);

```
<strong>Maximum results per page</strong>
```php
$pagination->setPerPage(100); // 100 results per page
```
<strong>Number of links to be show</strong>
```php
$pagination->setAdjacentLinks(8); // displays 8 links 
```
<strong>Customized CSS</strong>
The <code>paginate.css</code> CSS file comes included to get you started. The default class names for the links are:
```php
private $_divClass      = 'pagination';				// Main div class name holding the links
private $_currentClass  = 'current';				// Current active link span class name
private $_disableClass  = 'disabled';				// Non-active links span class name
```


If you're using a specific namespace, then you can modify the default class names by simply creating an array with the values you want:
```php
$css['paginate'] = 'myCustomPaginateName';
$css['current']  = 'myCustomCurrentName';
$css['disabled'] = 'myCustomDisabledName';

$pagination->setClasses($css);
```
Be sure that it matches what you have in your CSS file.

<strong>CSS File</strong>
```css
div.pagination {
	text-align:center;
	height:22px;
	line-height:21px;
	clear:both;
	padding:4px;
	min-width:350px;
	font-weight:bold;
	color:#DCDCDC;
}
div.pagination a:link {
	padding:7px;
	padding-top:2px;
	padding-bottom:2px;
	margin-left:7px;
	text-decoration:none;
	color:#707070;
	width:22px;
	font-weight:normal;
	-moz-box-shadow: 3px 3px 5px #D0D0D0;
	-webkit-box-shadow: 3px 3px 5px #D0D0D0;
	box-shadow: 3px 3px 5px #D0D0D0;
	border:solid 1px #828282;
}
div.pagination a:visited {
	color: #D56A00;
	border:solid 1px #828282;
}
div.pagination a:hover {
	color:#0072BC;
	text-decoration: underline;
}
div.pagination span.current {
	padding:7px;
	padding-top:2px;
	padding-bottom:2px;
	margin-left:7px;
	color:#353535;
	cursor:default;
	border:solid 2px #D0D0D0;
	background-color: #CCC;
}
div.pagination span.disabled {
	padding:7px;
	padding-top:2px;
	padding-bottom:2px;
	margin-left:7px;
	text-decoration:none;
	color:#CBCBCB;
	cursor:not-allowed;
	border:solid 1px #D0D0D0;
}
```
