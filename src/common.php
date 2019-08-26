<?php

error_reporting(0);
header("Content-Type: text/html; charset=utf-8");
session_start();

/**
 * Mysql_PDO
 * mysql_pdo.class.php
 */
class Mysql_PDO
{
    private $db_host;       //数据库主机
    private $db_user;       //数据库登陆名
    private $db_pwd;        //数据库登陆密码
    private $db_name;       //数据库名
    private $db_charset;    //数据库字符编码
    private $pdo;       //数据库连接

    public function __construct($db_host, $db_user, $db_pwd, $db_name, $db_charset = 'utf8')
    {
        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_pwd = $db_pwd;
        $this->db_name = $db_name;
        $this->db_charset =  $db_charset;
        try {
            $this->pdo = new PDO('mysql:host='.$this->db_host.';dbname='.$this->db_name.';charset='.$this->db_charset, $this->db_user, $this->db_pwd);
        } catch (PDOException $e) {
            die('DB Connect Error!');
        }
    }

    public function select($sql, $array=array())
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($array);
        return $stmt->fetchAll();
    }

    public function insert($sql, $array=array())
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($array);
        return $stmt->rowCount();
    }

    public function update($sql, $array=array())
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($array);
        return $stmt->rowCount();
    }

    public function delete($sql, $array=array())
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($array);
        return $stmt->rowCount();
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}

function get_random_str($length)
{
    $str = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $len = strlen($str) - 1;
    $randstr = '';
    for ($i = 0; $i < $length; $i ++) {
        $num = mt_rand(0, $len);
        $randstr .= $str[$num];
    }
    return $randstr;
}

function validator($data)
{
    return preg_match('/^[a-zA-Z0-9]+$/is', $data);
}

function url_validator($data)
{
    return preg_match('/^(http|https):\/\/(.*?)$/is', $data);
}

function profile_validator($data)
{
    return !preg_match('/iframe/is', $data);
}

/**
 * Database Configure
 */

$db_host = "127.0.0.1";
$db_user = "root";
$db_pwd = "root";
$db_name = "xss";


/**
 * Database Init
 */
$db = new Mysql_PDO($db_host, $db_user, $db_pwd, $db_name);
