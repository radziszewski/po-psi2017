<?PHP
$masterpass = "[YOUR-MASTER-PASS-VERY-SECRET]";
$key = md5($masterpass);
$date = time();
$keydate = md5($key.$date);
$period = 7*24*3600;
$loginCurrUser = "_none_";

function login()
{     
    global $keydate;
    global $date;
    global $period;
    if (isset($_SERVER['PHP_AUTH_PW'])) 
    {
        if (checkHash($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']))
        {
            $loginCurrUser = $_SERVER["PHP_AUTH_USER"];
            $sess = array();
            $data = array();
            $sess["data"] = $data;
            $sess["key"] = md5($keydate.$loginCurrUser.json_encode($sess["data"]));
            $sess["date"] = $date;
            $sess["user"] = $loginCurrUser;
            $cookie = base64_encode(json_encode($sess));
            setcookie("session",$cookie,$date+$period,"/");
            $_COOKIE["session"] = $cookie;
            return true;
        }
    }
    return false;
}

function checkLogin()
{
    global $loginCurrUser;
    global $key;
    global $date;
    global $period;
    if (isset($_COOKIE["session"]))
    {
        $sess = json_decode(base64_decode($_COOKIE["session"]));
        $sess_key = $sess->key;
        $sess_date = $sess->date;
        $loginCurrUser = $sess->user;
        if ($sess_key == md5(md5($key.$sess_date).$loginCurrUser.json_encode($sess->data)) && $sess_date >= $date-$period)
        {
            return true;
        }
    }
    return false;
}

$txt = "???";
$status = checkLogin();
if (!$status)
{
    $txt = "first check failed";
    if (login())
    {
        $txt = "second check failed";
        $status = checkLogin();
    }
}
if (!$status)
{
    header('WWW-Authenticate: Basic realm="My Realm"');
	header('HTTP/1.0 401 Unauthorized');
    die("Please log in (".$txt.")");
}

?>