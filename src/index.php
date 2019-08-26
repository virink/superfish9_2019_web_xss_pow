<?php

require_once "common.php";


function is_login()
{
    if (empty($_SESSION['username']) or $_COOKIE["BOTTOKEN"] != getenv("BOTTOKEN")) {
        die("用户没有登录<script>setTimeout(()=>{location.href='/index.php?a=login';}, 1000);</script>");
    }
}

$action = isset($_GET['a']) ? $_GET['a'] : "index";

switch ($action) {
    case 'login':
        if (strtolower($_SERVER['REQUEST_METHOD']) == "post") {
            if (empty($_POST['username']) && empty($_POST['password'])) {
                $_SESSION['msg'] = 'param can not be empty';
            } elseif (strlen($_POST['username']) > 20) {
                $_SESSION['msg'] = 'username too long';
            } elseif (!validator($_POST['username'])) {
                $_SESSION['msg'] = "invalid username";
            }
            $username = trim($_POST['username']);
            $result = $db->select(
                "SELECT * FROM users WHERE username=:username and password=:password limit 1",
                [":username" => $username, ":password" => md5($_POST['password'])]
            );
            if (count($result) > 0) {
                $_SESSION['id'] = $result[0]['id'];
                $_SESSION['username'] = $username;
                $_SESSION['profile_id'] = $result[0]['profile_id'];
                echo "登录成功, 跳转到用户主页<script>setTimeout(()=>{location.href='/index.php?a=user';}, 1000);</script>";
            } else {
                $_SESSION['msg'] = '登录失败, 用户名或密码错误';
            }
        } else {
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Login</title></head><body><h3>'. $_SESSION['msg'] .'</h3><form action="/index.php?a=login" method="POST">用户名:<br><input type="text" name="username"><br>密码:<br><input type="password" name="password"><br><br><input type="submit" value="Login"></form></body></html>';
        }
        break;
    case 'logout':
        session_start();
        session_destroy();
        echo "退出成功, 跳转到登录页面";
        echo "<script>location.href='/';</script>";
        break;
    case 'register':
        if (strtolower($_SERVER['REQUEST_METHOD']) == "post") {
            if (empty($_POST['username']) && empty($_POST['password'])) {
                $_SESSION['msg'] = 'param can not be empty';
            } elseif (strlen($_POST['username']) > 20) {
                $_SESSION['msg'] = 'username too long';
            } elseif (!validator($_POST['username'])) {
                $_SESSION['msg'] = "invalid username";
            }
            $username = trim($_POST['username']);
            $result = $db->select(
                "SELECT * FROM users WHERE username=:username",
                [":username" => $username]
            );
            if (count($result) > 0) {
                $_SESSION['msg'] = '注册失败, 用户名重复';
            } else {
                $profile_id = md5(get_random_str(32));
                $result = $db->insert(
                    "INSERT INTO users(username, password, profile_id) VALUES (:username, :password, :profile_id);",
                    [
                        ":username" => $username,
                        ":password" => md5($_POST['password']),
                        ":profile_id" => $profile_id,
                    ]
                );
                $db->insert(
                    "INSERT INTO profile(profile_id, content) VALUES (:profile_id, :content);",
                    [":profile_id" => $profile_id, ":content" => "default profile"]
                );
                if (!$result) {
                    $_SESSION['msg'] = '注册失败，数据库错误';
                } else {
                    echo "注册成功,跳转到登录页面<script>setTimeout(()=>{location.href='/index.php?a=login';}, 1000);</script>";
                }
            }
        } else {
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Register</title></head><body><h3>'. $_SESSION['msg'] .'</h3><form action="/index.php?a=register" method="POST">用户名:<br><input type="text" name="username"><br>密码:<br><input type="password" name="password"><br><br><input type="submit" value="Register"></form></body></html>';
        }
        break;
    case 'update':
        is_login();
        $new_profile = $_POST['profile'];
        $result = $db->update(
            "UPDATE profile SET content = :new_profile WHERE profile_id = :profile_id",
            [":new_profile" => $new_profile, ":profile_id" => $_SESSION['profile_id']]
        );
        if (!(count($result) > 0)) {
            echo "更新profile失败";
        } else {
            echo "更新profile成功";
        }
        echo "<script>setTimeout(()=>{history.back();}, 1000);</script>";
        break;
    case 'profile':
        is_login();
        $profile_id = $_GET['id'];
        if (validator($profile_id)) {
            $result = $db->select(
                "SELECT content FROM profile WHERE profile_id = :profile_id",
                [":profile_id" => $profile_id]
            );
            if (count($result) > 0) {
                echo "<script>console.log('Welcome to ks大型交友平台');</script>\n";
                echo "<script src=\"csp.php?nonce=".md5(get_random_str(32))."\"></script>\n";
                $content = $result[0]['content'];
                $result = $db->select(
                    "SELECT * FROM users WHERE profile_id = :profile_id",
                    [":profile_id" => $profile_id]
                );
                if (count($result) > 0 && $result[0]['id'] === $_SESSION['id']) {
                    echo 'Update Profile:<form action="/index.php?a=update" method="POST"><textarea rows="10" cols="50" name="profile">'.$content.'</textarea><br/><input type="submit" value="submit"></form>';
                } else {
                    echo '<textarea rows="10" cols="50" name="profile">'.$content.'</textarea>';
                }
            } else {
                die("no such profile id");
            }
        } else {
            die("invalid profile id");
        }
        break;
    case 'bug':
        is_login();
        if (strtolower($_SERVER['REQUEST_METHOD']) == "post") {
            $url = $_POST['url'];
            if ($_SESSION['pow'] == substr(md5($_POST['pow']), 0, 6)) {
                $_SESSION['msg'] = "pow error";
            } elseif (strlen($url) > 250) {
                $_SESSION['msg'] = "url too long";
            } elseif (!url_validator($url)) {
                $_SESSION['msg'] = "invalid url";
            } else {
                $result = $db->insert(
                    "INSERT INTO bugs(url) VALUES (:url)",
                    [":url" => $url]
                );
                if (count($result) > 0) {
                    $_SESSION['msg'] = "URL提交成功，管理员将会处理该Bug.";
                } else {
                    $_SESSION['msg'] = "URL提交失败，请联系管理员查明原因";
                }
            }
            echo "<script>setTimeout(()=>{location.href='/index.php?a=bug';}, 1000);</script>";
        } else {
            $_SESSION['pow'] = substr(md5(get_random_str(5)), 0, 6);
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Bug反馈</title></head><body><h1>Bug反馈</h1><h3>'.$_SESSION['msg'].'</h3><br/><form action="/index.php?a=bug" method="POST">URL: <br/><input type="text" name="url"><br/>PoW: substr(md5(?), 0, 6) === "'.$_SESSION['pow'].'" <br/><input type="text" name="pow"><br/><input type="submit" value="Submit"></form>';
        }
        break;
    case 'user':
        is_login();
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>ks大型交友平台</title></head><body><h3>欢迎来到ks交友</h3><br/><a href="/index.php?a=profile&id='.$_SESSION['profile_id'].'">个人交友资料(公开)</a><br/><a href="/index.php?a=bug">Bug反馈</a><br/>';
        break;
    case 'index':
    default:
        if (isset($_SESSION['username'])) {
            die("用户已经登录<script>setTimeout(()=>{location.href='/index.php?a=user';}, 1000);</script>");
        }
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>ks大型交友平台</title></head><body><h3>ks交友</h3><a href="/index.php?a=login">登录</a><a href="/index.php?a=register">注册</a><hr /></body></html>';
        break;
}
