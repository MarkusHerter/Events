<?php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="datepicker/dist/the-datepicker.min.js"></script>
    <link rel="stylesheet" href="datepicker/dist/the-datepicker.css">
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
        #main {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            height: 100%;
            width: 100%;

        }
        input, label {
            height: 1.5em;
        }
        label {
            display: inline-block;
        }
        .bigBtn{
            background-color: blanchedalmond;
            width: 8em;
            height: 3em;
            margin-top:0.5em;
            padding: 0 2px 0 2px;
        }
        @media (min-width: 768px){
            #inputdata {
                width: 380px;
                height: 90%;
            }
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
            #inputdata {
                width: 380px;
                height: 90%;
            }
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
<body onload="loadGroups()">
<h2 style="text-align: center; padding-top: 0.5em">Neues Event anlegen</h2>
<div id="leftMenu">
    <div style="width: 60%; margin:0 auto;">
        <button class="bigBtn" style="width: 100%;" disabled>Event starten</button>
    </div>
    <div style="width:60%; margin:0 auto;">
        <a href="index.php?action=eventlist"><button class="bigBtn" style="width: 100%;">alle Events</button></a>
    </div>
    <div style="width: 60%; margin: 0 auto;">
        <a href="index.php?action=myaccount"><button class="bigBtn" style="width: 100%;">Mein Account</button></a>
    </div>
</div>
<div id="main">

    <div id = "groups"></div>
    <div id="inputdata">
        <label style="display:block; width: 100%">Was: <input type="text" style="width: 100%" name="nameEvent" placeholder="Volleyball" required></label><br><br>
        <input type="text" name="date" id="dateforsend" required hidden><div id="date"></div><br>
        <div style="padding-bottom: 0.5em; display:flex; justify-content: space-evenly;">
            <label><input type="radio" name="repeat" value="0">einmalig</label>
            <label><input type="radio" name="repeat" value="7">wöchentlich</label>
            <label><input type="radio" name="repeat" value="2">alle<input type = 'number' onclick="document.getElementsByName('repeat')[2].checked='true';" min="1" max="365" step="1" id='rate' style="width:2em"> Tage</label>
            <script>
                document.getElementsByName('repeat')[0].checked='true';
            </script>
        </div>
        <label>Start: <input type="time" name="startTime" required></label>
        <label style="float: right;">Ende: <input type="time" name="endTime"></label><br>
        <label>Ort: <input type="text" name="ort" value="Berlin" style="width: 100%;"required></label><br><br>
        <label>min. Teilnehmer: <input type="number" name="min" min="1" step = "1" style="width:3em"></label>
        <label style="float:right;">max. Teilnehmer: <input type="number" name="max"  min="1" step = "1" style="width:3em;"></label><br>
        <label>Info:<br><textarea name="info" style="width: 380px; height: 4em;"></textarea></label><br><br>
        <div id="groups" class="groupdiv">
        </div>
        <br>
        <div style="text-align: center; width: 100%; padding-bottom:3em;"><button class="bigBtn" type="button" onclick="sendData()">Event senden</button></div>
    </div>

</div>
<div id="bottomMenu">
    <div style="width: 33.3%">
        <button class="bigBtn" style="width: 100%;" disabled>Event starten</button>
    </div>
    <div style="width:33.3%">
        <a href="index.php?action=eventlist"><button class="bigBtn" style="width: 100%;">alle Events</button></a>
    </div>
    <div style="width: 33.3%">
        <a href="index.php?action=myaccount"><button class="bigBtn" style="width: 100%;">Mein Account</button></a>
    </div>
</div>
</body>
<script>
    function sendData(){
        let nameEvent = document.getElementsByName('nameEvent')[0].value;
        let date = document.getElementsByName('date')[0].value;
        if (date ===""){
            window.alert("Bitte Datum eingeben");
            return 0;
        }
        let rate = document.querySelector('input[name="repeat"]:checked').value;
        if (rate==='2'){
            if (Number(document.getElementById('rate').value) !== 0 && Number.isInteger(Number(document.getElementById('rate').value))){
                rate = Number(document.getElementById('rate').value);
            } else {
                window.alert("Nur ganze Tage");
                return 0;
            }
        }
        let startTime = document.getElementsByName('startTime')[0].value;
        if (startTime ===""){
            window.alert("Bitte eine Startzeit eingeben");
            return 0;
        }
        let endTime = document.getElementsByName('endTime')[0].value;
        let ort = document.getElementsByName('ort')[0].value;
        if (ort===""){
            window.alert("Bitte einen Ort eingeben");
            return 0;
        }
        let min = document.getElementsByName('min')[0].value;
        let max = document.getElementsByName('max')[0].value;
        let info = document.getElementsByName('info')[0].value;

        let xhttp = new XMLHttpRequest();
        xhttp.onload = function (){
            if (xhttp.responseText === "ok"){
                window.open('eventlist.php','_self');
            } else {
                console.log(xhttp.responseText);
            }
        }
        xhttp.open('POST', 'index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        let dataString = "nameEvent="+encodeRFC5987ValueChars(nameEvent)+"&date="+date+"&startTime="+startTime+"&endTime="+endTime+"&ort="+
        ort+"&minPers="+min+"&maxPers="+max+"&info="+info+"&rate="+rate;
        let n= 0;
        for (let elem of document.getElementsByName('group')){
            if (elem.checked) {
                dataString += "&groups[" + n + "]=" + elem.value;
                n++;
            }
        }
        if (n===0){
            window.alert('Bitte mindestens eine Gruppe auswählen, in der die Veranstaltung gepostet werden soll.');
            return 0;
        }
        xhttp.send("action=saveEvent&"+dataString);
    }
    function encodeRFC5987ValueChars (str) {
        return encodeURIComponent(str).
            replace(/['()]/g, escape). // i.e., %27 %28 %29
            replace(/\*/g, '%2A').
            // Die folgenden Zeichen müssen nicht nach RFC5987 kodiert werden,
            // daher können wir bessere Lesbarkeit übers Netzwerk sicherstellen:
            // |`^
            replace(/%(?:7C|60|5E)/g, unescape);
    }
    (function picker () {
        const container = document.getElementById('date');
        const datepicker = new TheDatepicker.Datepicker(null, container);
        let today = new Date();
        let earliest = today.getFullYear()+'/'+(today.getMonth()+1)+'/'+today.getDate();
        let latest = (today.getFullYear()+1)+'/'+(today.getMonth()+1)+'/'+today.getDate();
        console.log("dskfjdkfd");
        console.log(Date.parse(latest));
        datepicker.options.setMinDate(earliest);
        datepicker.options.setMaxDate(latest);
        datepicker.options.onSelect((event, day) => {
            document.getElementById('dateforsend').value = day.getFormatted();
        });
        datepicker.render();
    })();
    function loadGroups(){
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function () {
            if (xhttp.status === 200) {
                groups = JSON.parse(xhttp.responseText);
                buildGroupSelect();
            }
        }
        xhttp.open('POST', 'index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send("action=loadGroups");
    }
    function buildGroupSelect(){
        for (let group of groups) {
            document.getElementsByClassName('groupdiv')[0].innerHTML +=
                "<label style='margin:1em 1em 0 1em'><input type='checkbox' name = 'group' class='groupCheck' value='"
                + group['id'] + "' required> " + group['name'] + "</input></label>";
        }
        for (let elem of document.getElementsByClassName('groupCheck')){
 //            elem.oninvalid= function (){
  //               this.setCustomValidity("Mindestens eine Gruppe muss ausgewählt werden.");
   //          }
            elem.onchange = function (){
   //             this.setCustomValidity("");
                for (let elem2 of document.getElementsByClassName('groupCheck')){
                    if (elem2.checked === true){
                        for (let elem3 of document.getElementsByClassName('groupCheck')){
                            elem3.required = false;
                        }
                        return true;
                    }
                }
                for (let elem3 of document.getElementsByClassName('groupCheck')){
                    elem3.required = true;
                }
            }
        }
    }
</script>
</html>

