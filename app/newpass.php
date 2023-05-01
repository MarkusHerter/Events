<!DOCTYPE html>
<html sty lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        #flexcontainer {
            margin-top: 10%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-collapse: collapse;
        }
        .smallBtn{
            background-color:floralwhite;
            width: 8em;
            height: 2em;
            margin-top:0.5em;
            padding: 0 2px 0 2px;
        }
        input {
            width: 10em;
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
                <tr><td>Email-Adresse:</td><td><input name="email" type="text" value=""></td></tr>
                <tr><td style="padding-top:0.5em; text-align: center"><button type="submit" class="smallBtn" name="action" value="sendmail">Send Mail</button></td><td colspan="2" style="padding-top: 0.6em; text-align: right; font-size: small" >
                        <a href="login.php">zurück</a></td></tr>
            </table>
            <br>
        </form>
    </div>
</div>
</body>

</html>