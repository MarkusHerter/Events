<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="imagecropper/cropper.js"></script>
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <link href="https://www.cssscript.com/wp-includes/css/sticky.css" rel="stylesheet" type="text/css">
    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
        * {
            margin:0;
            padding:0;
        }
        html, body{
            width: 100%;
            height: 100%;
            background-color: peru;
        }
        .groupTable {
            border-collapse: separate;
            border-spacing: 0 4px;
            table-layout: fixed;
        }

        .groupTable>tbody>tr {
            box-shadow: 2px 2px 2px dimgrey;
            background-color: burlywood;
            cursor: pointer;
        }
        .groupTable>tbody>tr:hover {
        }
        .groupTable>tbody>td {
            padding: 0.5em 0.5em 0.5em 0.5em;

        }
        .groupTable>thead>th {
            font-size: x-large;
            text-align: left;
            background-color: peru;
            border: solid 4px peru;
        }
        table {
            width: 100%;
            table-layout: fixed;
            margin-top:1em;
        }
        #main {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            height: fit-content;
            width: 100%;
        }
        input{
            padding:2px;
        }
        label{
            display: inline-block;
            padding: 0.5em 0 0.5em 0;
            width: 100%;
        }
        td {
            padding: 2px 2px 2px 2px;
        }
        .bigBtn{
            background-color: blanchedalmond;
            width: 8em;
            height: 3em;
            margin-top:0.5em;
            padding: 0 2px 0 2px;
        }
        <?php if (!Group::loadGroupsByUserId($_SESSION['id'])){
            echo '@keyframes glowing {';
            echo '0% { background-color: #ffebcd; box-shadow: 0 0 5px #ffebcd; }';
            echo '50% { background-color: #ffffff; box-shadow: 0 0 20px #ffffff; }';
            echo '100% { background-color: #ffebcd; box-shadow: 0 0 5px #ffebcd; }}';
            echo '#groups>div>.bigBtn {';
            echo 'animation: glowing 1300ms infinite;}';
            }?>
        .smallBtn{
            background-color: blanchedalmond;
            width: 8em;
            height: 2em;
            margin-top:0.5em;
            padding: 0 2px 0 2px;
        }
        h1  {
            text-align: center;
        }
        @media (min-width: 768px){
            #leftMenu {
                width: 20%;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                position: fixed;
                left: 0;
                top:3em;
            }
            #bottomMenu{
                display: none;
            }
        }
        @media (max-width: 767px){
            #leftMenu {
                display:none;
            }
            #bottomMenu {
                display: flex;
                position: fixed;
                bottom: 0;
                right: 0;
                width: 100%;
                flex-direction: row;
                justify-content:start;
            }
        }
    </style>


</head>
<body onload="loadPic();loadGroups();loadEvents()">
<h2 style="text-align: center; padding: 0.5em 0 0.5em 0;">Mein Account</h2>
<div id="leftMenu">
    <div style="width: 60%; margin:0 auto;">
        <a href="index.php?action=newevent"><button class='bigBtn' style="width: 100%; height: 3em">Event starten</button></a>
    </div>
    <div style="width:60%; margin:0 auto;">
        <a href="index.php?action=eventlist"><button class='bigBtn' style="width: 100%; height: 3em">alle Events</button></a>
    </div>
    <div style="width: 60%; margin: 0 auto;">
        <button class='bigBtn' style="width: 100%; height: 3em;" disabled>Mein Account</button>
    </div>
</div>
<div id="main" >
    <div style="display: flex; width: 380px; align-items: center; justify-content: space-between;margin: .5em 0 .5em 0;">
        <button  style="width: 6em; visibility: hidden;"></button>
        <div id="user"><h3><?php echo $_SESSION['name']?></h3></div>
        <form action="index.php" method="GET"><input  style=" background-color: burlywood; width: 6em; height: 2em;" type="submit" name="logout" value="logout"></form>
    </div>
    <div id="userpic"></div>
    <div style='display:flex; width: 380px; justify-content:space-evenly; margin-bottom:0.5em;'>
        <button class='bigBtn' id='passBtn' onclick="changePassword()">Neues Passwort</button>
        <button class='bigBtn' type="button" id="btnShowCropper" onclick="showCropper();">Profilbild hochladen</button></div>
    <div id="passwd" style="display:none; width: 380px;"><label>Aktuelles Passwort: <input type="text" id="passwdOld" style="float:right;"></label><br>
        <label>Neues Passwort: <input type="text" id="passwdNew" style="float: right;"></label><br>
    <button class='smallBtn' type="button" onclick="sendPasswd();">abschicken</button></div>

    <div id="canvasInside" style="width:fit-content; height: fit-content; display:none; flex-direction: column; align-items: center;">
        <canvas id="testCanvas" width="380" height="380" style="display:none"></canvas>
        <input type="file" id="fileInput" onchange="handleFileSelect()" value="" accept="img/*">
        <div style='display:flex; width: 380px; justify-content:space-evenly; margin-bottom:0.5em;'>
            <button class='bigBtn' type="button" id='buttonCropSave' onclick="cropPic(this);" value="">Ausschneiden</button>
            <button class='bigBtn' type="button" onclick="setCropperBack();">zurück</button>
        </div>
    </div>

    <h3 style="margin-top:1.5em;">Meine Events</h3>
    <div style="width: 380px; padding:1em 0 3em 0;">
        <table><thead></thead><th style='width: 25%'></th><th style='width: 15%'></th><th style='width: 35%'></th><th style='width: 20%; '></th></thead><tbody id="myStartedEvents"></tbody></table>
    </div>
    <h3 style="margin-top:1.5em;">Meine Gruppen</h3>
    <div id="groups" style="width: 380px; padding:1em 0 5em 0;">
        <div style='display:flex; justify-content:space-evenly; margin-bottom:0.5em;'>
            <button class='bigBtn' style='float:none'type='button' id="buttonNewGroup" onclick="addGroup();">neue Gruppe anlegen</button>
            <button class='bigBtn' style='float:none'type='button' id="buttonJoinGroup" onclick="joinGroup();">einer Gruppe beitreten</button>
        </div>
        <div id="newGroup" style="width:100%; display:none; flex-direction: column; justify-content: left; margin: 1.5em 0 1.5em 0;">
            <label style="display:block; width:50%;">Name der Gruppe: <input type="text" id="groupName"></label>
            <label><input type="checkbox" id="private" unchecked>Nur ich darf einladen</label>
            <label><input type="checkbox" id="privateEvents" unchecked>Nur ich darf Events starten</label>
            <button class='smallBtn' onclick="sendNewGroup();">erstellen</button>
        </div>
        <div id="joinGroup" style="width:100%; display:none; flex-direction: row; justify-content: left; flex-wrap: wrap; align-items: flex-start; margin: 1.5em 0 .5em 0;">
            <label style="display:block; width:50%;">Name der Gruppe: <input type="text" id="joinName"></label>
            <label style="display:block; width:50%;">Passwort: <input type="text" id="joinPass"></label>
            <button class='smallBtn' onclick="sendJoinGroup();">beitreten</button>
        </div>
        <div>
        </div>
        <table class="groupTable">
            <thead><th style='width:35%; text-align: left'>Name</th><th style='width:35%; text-align: left;'>Passwort</th><th style='width:30%;'></th></thead>
            <tbody id='groupBody'></tbody>
        </table>
    </div>
</div>
<div id="bottomMenu">
    <div style="width: 33.3%">
        <a href="index.php?action=newevent"><button class='bigBtn' style="width: 100%; height: 3em">Event starten</button>
    </div>
    <div style="width:33.3%">
        <a href="index.php?action=eventlist"><button class='bigBtn' style="width: 100%; height: 3em">alle Events</button></a>
    </div>
    <div style="width: 33.3%">
        <button class='bigBtn' style="width: 100%; height: 3em;" disabled>Mein Account</button></a>
    </div>
</div>
</body>
<script>
    function loadPic() {
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function () {
            if (JSON.parse(xhttp.responseText)[1]) {
                document.getElementById('userpic').innerHTML = "<img style='width: 200px; height: 200px;' src='" + JSON.parse(xhttp.responseText)[1] + "'>";
            }
        }
        xhttp.open('POST', 'index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send('action=loadUserPic');
    }
     // initialize cropper by providing it with a target canvas and a XY ratio (height = width * ratio)
    function handleFileSelect() {
        document.getElementById("testCanvas").style.display='flex';
        // this function will be called when the file input below is changed
        let file = document.getElementById("fileInput").files[0];  // get a reference to the selected file
        let reader = new FileReader(); // create a file reader
        // set an onload function to show the image in cropper once it has been loaded
        reader.onloadend = function(event) {
            let data = event.target.result; // the "data url" of the image
            cropper.showImage(data); // hand this to cropper, it will be displayed
            cropper.startCropping();

        }

        reader.readAsDataURL(file); // this loads the file as a data url calling the function above once done
    }
    function resizedataURL(datas, wantedWidth, wantedHeight) {
        return new Promise(async function (resolve, reject) {
            // We create an image to receive the Data URI
            var img = document.createElement('img');
            // When the event "onload" is triggered we can resize the image.
            img.onload = function () {
                // We create a canvas and get its context.
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');
                // We set the dimensions at the wanted size.
                canvas.width = wantedWidth;
                canvas.height = wantedHeight;
                // We resize the image with the canvas method drawImage();
                ctx.drawImage(this, 0, 0, wantedWidth, wantedHeight);
                var dataURI = canvas.toDataURL();
                // This is the return of the Promise
                resolve(dataURI);
            };
            // We put the Data URI in the image's src attribute
            img.src = datas;
        })
    }
    function cropPic(){
        let base64array = [];
        base64array[0] = cropper.getCroppedImageSrc();
        let promi = resizedataURL(base64array[0], 200,200);
        promi.then(function(value) {
            base64array[0] = value;
            let elem = document.getElementById('buttonCropSave');
            elem.innerHTML = 'speichern';
            elem.onclick = function () {
                let blobData = new Blob(base64array);
                let data = new FormData();
                data.append('pic', blobData);
                data.append('action', 'savePic');
                let xhttp = new XMLHttpRequest();
                xhttp.onload = function () {
                    document.getElementById('canvasInside').style.display = 'none';
                    elem.innerHTML = 'ausschneiden';
                    elem.onclick = cropPic;
                    hideCropper();
                    loadPic();
                }
                xhttp.open('POST', 'index.php');
                xhttp.send(data);
            }
        })
    }
    cropper.start(document.getElementById("testCanvas"), 1);// initialize cropper by providing it with a target canvas and a XY ratio (height = width * ratio)

    function loadEvents(){
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function (){
            if (xhttp.status === 200){
                buildEventTable(JSON.parse(xhttp.responseText));
            }
        }
        xhttp.open('POST', 'index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send("action=loadIniEvents");
    }
    function sendJoinGroup(){
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function (){
            if (xhttp.status === 200){
                if (xhttp.responseText === 'ok') {
                    document.getElementById('joinPass').value="";
                    document.getElementById('joinName').value="";
                    document.getElementById('buttonNewGroup').style.animation = 'unset';
                    document.getElementById('buttonJoinGroup').style.animation = 'unset';
                    loadGroups();
                } else {
                    window.alert(xhttp.responseText);
                }
            }
        }
        xhttp.open('POST', 'index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send("action=joinGroup&name="+document.getElementById('joinName').value+"&passwd="+document.getElementById('joinPass').value);
    }
    function setCropperBack(){
        let elem = document.getElementById('buttonCropSave');
        elem.innerHTML='ausschneiden';
        elem.onclick=cropPic;
        cropper.restore();
        cropper.startCropping();
    }
    function showCropper(){
        resetPassword();
        resetAddGroup();
        resetJoinGroup();
        let elem = document.getElementById('btnShowCropper');
        elem.style.color='red';
        document.getElementById('canvasInside').style.display="flex";
        cropper.start(document.getElementById("testCanvas"), 1);
        elem.onclick= hideCropper;
    }
    function hideCropper(){
        document.getElementById('btnShowCropper').style.color="black";
        document.getElementById('btnShowCropper').onclick = showCropper;
        document.getElementById('canvasInside').style.display='none';
    }
    function joinGroup(){
        resetPassword();
        resetAddGroup();
        hideCropper();
        document.getElementById('joinGroup').style.display='flex';
        document.getElementById('buttonJoinGroup').style.color='red';
        document.getElementById('buttonJoinGroup').onclick = resetJoinGroup;
    }
    function resetJoinGroup(){
        document.getElementById('buttonJoinGroup').style.color='black';
        document.getElementById('joinGroup').style.display='none';
        document.getElementById('buttonJoinGroup').onclick=joinGroup;
    }
    function addGroup(){
        resetPassword();
        resetJoinGroup();
        hideCropper();
        document.getElementById('newGroup').style.display='flex';
        document.getElementById('buttonNewGroup').style.color='red';
        document.getElementById('buttonNewGroup').onclick = resetAddGroup;
    }
    function resetAddGroup(){
        document.getElementById('buttonNewGroup').style.color='black';
        document.getElementById('newGroup').style.display='none';
        document.getElementById('buttonNewGroup').onclick=addGroup;
    }
    function changePassword(){
        resetAddGroup();
        resetJoinGroup();
        hideCropper();
        document.getElementById('passwd').style.display='block';
        document.getElementById('passBtn').style.color='red';
        document.getElementById('passBtn').onclick= resetPassword;
    }
    function resetPassword(){
        document.getElementById('passBtn').style.color='black';
        document.getElementById('passwdNew').value='';
        document.getElementById('passwdOld').value='';
        document.getElementById('passwd').style.display='none';
        document.getElementById('passBtn').onclick=changePassword;
    }
    function sendNewGroup(){
        let xhttp = new XMLHttpRequest();
        xhttp.onload= function (){
            if (xhttp.status === 200){
                document.getElementById('buttonNewGroup').style.animation = 'unset';
                document.getElementById('buttonJoinGroup').style.animation = 'unset';
                document.getElementById('groupName').value ="";
                console.log(xhttp.responseText);
                loadGroups();
            }
        }
        let privacy = document.getElementById('privateEvents').checked?2:0;
        privacy += document.getElementById('private').checked?1:0;
        console.log("Privacy: "+ privacy);
        xhttp.open('POST', 'index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send("action=saveNewGroup&name="+document.getElementById('groupName').value+"&privacy="+privacy);
    }
    function sendPasswd(){
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function (){
            if (xhttp.status === 200){
                    window.alert(xhttp.responseText);
                    if (xhttp.responseText ==='Passwort geändert') {
                        resetPassword();
                    }
                }
            }
        xhttp.open('POST','index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send('action=changePassword&passwdOld='+document.getElementById('passwdOld').value+'&passwdNew='+document.getElementById('passwdNew').value);
    }

    function loadGroups(){
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function () {
            if (xhttp.status === 200) {
                buildGroupTable(JSON.parse(xhttp.responseText));
            }
        }
        xhttp.open('POST', 'index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send("action=loadGroups&groupView=1");

    }
    function buildGroupTable(groups){
        document.getElementById('groupBody').innerHTML="";
        for (let group of groups){
            let showPasswd="";
            let shareButton="";
            if (group['privacy'] === 0 || group['privacy']===2){
                showPasswd = group['passwd'];
                shareButton = '<button id="shareBtn'+group['id']+'" type="button" class="smallBtn" onclick="share('+group['id']+',\x27'+group['name']+'\x27,\x27'+group['passwd']+'\x27,\x27'+group['privacy']+'\x27); event.stopPropagation()">einladen</button></td>';
            }
            if (group['owner']===<?php echo $_SESSION['id']?>){
                shareButton = '<button id="shareBtn'+group['id']+'" type="button" class="smallBtn" onclick="share('+group['id']+',\x27'+group['name']+'\x27,\x27'+group['passwd']+'\x27,\x27'+group['privacy']+'\x27); event.stopPropagation();">einladen</button></td>';
            }
            let row = document.createElement('tr');
            row.id= 'gr'+group.id;
            row.style.height = "4em";
            row.innerHTML = "<td>"+group['name']+"</td><td>"+showPasswd+
                "</td><td><button class='smallBtn' type='button' onclick='leaveGroup("+group['id']+")'>abmelden</button><br>"+ shareButton;
            row.onclick= function(){
                showGroupInfo(group);
            }
            document.getElementById('groupBody').appendChild(row);
        }
    }
    function showGroupInfo(group){
        try {
            document.getElementById('enterEmail').previousSibling.lastChild.lastChild.click();
        } catch{}
        try {
            document.getElementById('groupInfo').previousSibling.click();
        } catch{}
        let groupInfo= document.createElement('tr');
        groupInfo.id ='groupInfo';
        groupInfo.style.boxShadow='none';
        groupInfo.style.backgroundColor='peru';
        let whoInvites="Jeder darf einladen";
        if (group['privacy']==1 || group['privacy']==3){
            if (group['owner'] == <?php echo $_SESSION['id']?>){
                whoInvites = "Nur ich darf einladen";
            } else {
                whoInvites = "Nur der Ersteller darf einladen";
            }
        }
        let whoStartsEvents =" Jeder darf Events starten";
        if (group['privacy']==2 || group['privacy']==3){
            if (group['owner'] == <?php echo $_SESSION['id']?>){
                whoStartsEvents = "Nur ich darf Events starten";
            } else {
                whoStartsEvents = "Nur der Ersteller darf Events starten";
            }
        }
        groupInfo.innerHTML='<td colspan="3"><div style="width:390px; display:flex; flex-direction: column; align-items: center; flex-wrap: wrap;  margin: 0 0 1.5em 0;">'+
            '<div>'+whoInvites+'</div><div>'+whoStartsEvents+'</div><div style="margin: 1em 0 .5em 0">Wir sind dabei:</div><div id="teilnehmer"></div></td>';
        document.getElementById('gr'+group.id).insertAdjacentElement('afterend', groupInfo);
        document.getElementById('gr'+group.id).onclick= function(){
            document.getElementById('groupInfo').remove();
            document.getElementById('gr'+group.id).onclick = function(){
                showGroupInfo(group);
            }
        }
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function (){
            let teilnehmer = JSON.parse(xhttp.responseText);
            for (let person  of teilnehmer) {
                let personDiv = document.createElement('div');
                personDiv.style.display = 'flex';
                personDiv.style.flexDirection = 'column';
                personDiv.style.alignItems = 'center';
                personDiv.style.justifyContent = 'end';
                personDiv.style.marginRight="0.2em";
                let bild = "";
                if (person[1] != null) {
                    bild = "<img style='padding: 0 .3em 0 .3em; width:50px; height: 50px' src='" + person[1] + "'>";
                }
                personDiv.innerHTML = bild + person[0];
                let people = document.getElementById('teilnehmer');
                people.style.display= 'flex';
                people.style.flexWrap= 'wrap';
                people.style.justifyContent = 'left';
                people.appendChild(personDiv);
            }
        }
        xhttp.open('POST','index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send('action=loadTeilnehmer&groupId='+group.id);

    }
    function share(id, name,passwd, privacy){
        function shareIt(code){
            function sendEmail(){
                let xhttp = new XMLHttpRequest();
                xhttp.onload = function (){
                    if (xhttp.status === 200){
                        console.log(xhttp.responseText);
  //                      document.getElementById('mailsend').innerHTML=xhttp.responseText;
                        document.getElementById('sendtoemail').value="";
                    }
                }
                xhttp.open('POST','index.php');
                xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                let sendString = 'action=sendInvitation&email='+document.getElementById('sendtoemail').value+"&gr="+name+"&gp="+passwd;
                if (privacy===1 || privacy==3){
                    sendString += '&code='+code;
                }
                console.log(encodeURI(sendString));
                xhttp.send(encodeURI(sendString));

            }
            let urlName = encodeURI(name);
            let urlString = 'https://herter-dev.de/index.php?gr=' + urlName + "&gp=" + passwd;
            console.log(urlString);
            if (privacy==1 || privacy ==3) {
                urlString += "&code=" + code;
            }
            document.getElementById('shareBtn'+id).innerHTML='zurück';
            document.getElementById('shareBtn'+id).onclick= function (){
                document.getElementById('enterEmail').remove();
                document.getElementById('shareBtn'+id).innerHTML='einladen';
                document.getElementById('shareBtn'+id).onclick= function(){
                    share(id,name,passwd,privacy);
                    event.stopPropagation();
                }
                event.stopPropagation();
            }
            let email = document.createElement('tr');
            email.id ='enterEmail';
            email.style.boxShadow='none';
            email.style.backgroundColor='peru';
            email.innerHTML='<td colspan="3"><div style="width:390px; display:flex; flex-direction: column; align-items: center; flex-wrap: wrap;  margin: 0 0 1.5em 0;">'+
                '<div style="display: flex; margin: 1em 0 1em 0;"><label style="display:block;"><input type="text" id="sendtoemail"><br>Email-Adresse</label>'+
                '<button class="smallBtn" id="sendEmail">abschicken</button></div><div id="qrcode" ></div></div></td>';
            document.getElementById('gr'+id).insertAdjacentElement('afterend', email);
            var qrcode = new QRCode(document.getElementById("qrcode"), {
                text: urlString,
                width: 128,
                height: 128,
                colorDark : "black",
                colorLight : "blanchedalmond",
                correctLevel : QRCode.CorrectLevel.H
            });
            document.getElementById('sendEmail').onclick=function() {
                sendEmail();
            }

            try {
                navigator.share({
                    title: 'Share API',
                    text: name,
                    url: urlString,
                });
                document.getElementById('enterEmail').scrollIntoView();
            } catch(err) {}
        }
        try {
            document.getElementById('enterEmail').previousSibling.lastChild.lastChild.click();
        } catch{}
        try {
            document.getElementById('groupInfo').previousSibling.click();
        } catch{}
        if (privacy==='1'){
            let xhttp = new XMLHttpRequest();
            xhttp.onload = function(){
                console.log(xhttp.responseText);
                let code = xhttp.responseText;
                shareIt(code);
            }
            xhttp.open('POST','index.php');
            xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhttp.send('action=getInvitationCode&id='+id);
        } else {
            shareIt("");
        }
    }

    function leaveGroup(id){
        let leave = window.confirm('Gruppe wirklich verlassen?');
        event.stopPropagation();
        if (!leave){
            return 0;
        }
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function (){
            if (xhttp.status === 200){
               loadGroups();
            }
        }
        xhttp.open('POST', 'index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send("action=leaveGroup&groupId="+id);
    }
    function getNiceDateFormat(uglyDate, version) {
        const week = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
        let day = new Date(uglyDate);
        return week[day.getDay()] +", "+ day.getDate() + "." + (1 + day.getMonth()).toString();
    }
    function buildEventTable(events){
        for (let singleEvent of events){
            let row = document.createElement('tr');
            row.id = 'event'+singleEvent['id'];
            row.value = 0;
            let rate = "";
            row.innerHTML += "<td>"+getNiceDateFormat(singleEvent['day'])+"<br>"+showRate(singleEvent.rate)+"</td><td>"+singleEvent['start'].slice(0,-3)+
                "</td><td>"+singleEvent['type']+ "</td><td><button id = 'changeBtn' style= 'width: 6em' class='smallBtn'>ändern</button><br>" +
                "<button class='smallBtn' id='changeBtn' style= 'width: 6em' onclick='deleteEvent("+ singleEvent['id']+");'>absagen</button></td>";
            if (singleEvent['canceled']){
                row.style.backgroundImage = "url('https://herter-dev.de/ressources/cancelled.png')";
                row.style.backgroundPosition = 'center';
                row.style.backgroundRepeat = 'no-repeat';
                row.style.backgroundSize = 'contain';
                row.lastChild.firstChild.disabled = true;
                row.lastChild.lastChild.disabled = true;
            }
            document.getElementById('myStartedEvents').appendChild(row);
            document.getElementById('event'+singleEvent['id']).lastChild.firstChild.addEventListener('click', function(){
                changeInfo(singleEvent);
            })
        }
    }
    function showRate(rate){
        if (rate == 0) {
            return "";
        }
        if (rate == 1){
            return "täglich";
        }
        if (rate == 7) {
            return 'wöchentlich';
        }
            return 'alle '+ rate + ' Tage';
    }
    function changeInfo(event){
        let id = event.id;
        let row =document.getElementById('event' + id);
        row.value = row.value === 0 ? 1 : 0;
        if (document.getElementById('changeInfo') != null){
            document.getElementById('changeInfo').previousSibling.value = 0;
            document.getElementById('changeInfo').remove();
        }
        if (row.value === 1){
            let changeInfo = document.createElement('div');
            changeInfo.id = 'changeInfo';
            changeInfo.style.width = '380px';
            changeInfo.style.display = 'flex';
            changeInfo.style.flexDirection = 'column';
            changeInfo.style.alignItems = 'left';
            row.insertAdjacentElement("afterend", changeInfo);
            let rateChecker ="";
            if (event.rate != 0){
                rateChecker = '<div style="display:flex; justify-content: start;">'+
                    '<label><input type="checkbox" id="repeat">nicht mehr wiederholen</label></div>';
            }
            changeInfo.innerHTML = rateChecker + '</div><p>Infos:<br>' + event.info + '</p>' +
                '<textarea id="newInfo" style="width: 100%; height:6em;"></textarea>' +
                '<button id="sendInfo" type="button" class="smallBtn">send</button>';
            document.getElementById('sendInfo').addEventListener('click',function(){
                sendInfo(event);
            })
        }
    }
    function sendInfo(event){
        if (event.rate !== 0) {
            if (document.getElementById('repeat').checked) {
                event.rate = 0;
            }
        }
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function () {
            if (xhttp.responseText === 'ok') {
                document.getElementById('event'+event.id).firstChild.innerHTML=getNiceDateFormat(event['day'])+"<br>"+showRate(event.rate);
                changeInfo(event);
                if (textNew !=="") {
                    changeInfo(event);
                }
            }
        }
        xhttp.open('POST', 'index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        let textNew="";
        if (document.getElementById('newInfo').value !== "") {
            let today = new Date;
            let date = today.getDate() + '.' + (today.getMonth() + 1);
            let time = today.getHours() + ':' + (today.getMinutes() < 10 ? '0' + today.getMinutes() : today.getMinutes());
            textNew = "<br><span style='color:red'>" + date + ' ' + time + " </span>" + document.getElementById('newInfo').value;
        }
        event.info = event.info + textNew;
        xhttp.send("action=change&event=" + event.id + "&info=" + textNew + "&rate="+event.rate);
    }

    function deleteEvent($id){
        let absagen = window.confirm("Wirklich das Event absagen? Solange noch jemand als Teilnehmer in dem Event ist, wird es weiter angezeigt. Wenn keiner mehr drin ist, verschwindet es aus der Liste. ");
        if (absagen===false){
            return;
        }
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function (){
            if (xhttp.status === 200){
                document.getElementById('myStartedEvents').innerHTML="";
                loadEvents();
            }
        }
        xhttp.open('POST', 'index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send("action=deleteEvent&eventId="+$id);
    }
</script>
</html>