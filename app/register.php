<!DOCTYPE html>
<html sty lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            width: 95vw;
            height: 90vh;
            background-color: peru;
        }
        .smallBtn{
            background-color:floralwhite;
            width: 8em;
            height: 2em;
            margin-top:0.5em;
            padding: 0 2px 0 2px;
        }
        #flexcontainer {
            margin-top: 10%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-collapse: collapse;
        }
        #login {
            display:flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            width: 300px;
            height: 200px;
            background-color: blanchedalmond;
            box-shadow: 5px 5px 5px dimgrey;
        }
        input {
            width: 10em;
        }
    </style>
</head>
<body>
<div id="errMsg" style="text-align: center; margin-top:5%">
    <?php if (isset($e)){
        echo $e->getMessage();
    }  else {
        echo "&nbsp;";
    }?>
</div>
<div id="flexcontainer">
<div id = "login">
    <form method="post" action="index.php">
        <table>
            <tr><td colspan="2" style="text-align: right; font-size: small" >
                    <a href="login.php">Login</a></td></tr>
            <tr><td>Name:</td><td><input name="name" type="text" value='<?php echo $_POST['name'] ?? ""?>'></td></tr>
            <tr><td>Email:</td><td><input name="email" type = "text" value='<?php echo $_REQUEST['email'] ?? ""?>'></td></tr>
            <tr><td>Passwort:</td><td><input name="passwd" type="text" value='<?php echo $_REQUEST['passwd'] ?? ""?>'></td></tr>
            <tr><td colspan="2" style="text-align: center"><input type="submit" class="smallBtn" name="action" value="register" id="button"></td></tr>

        </table>
        <br>
    </form>
</div>
</div>
</body>

</html>
