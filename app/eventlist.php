<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            padding: 0;
            margin: 0;
        }
        html {
            height: 100%;
            width: 100%;
        }
        body {
            background-color: peru;
            width: 100%;
            height: 100%;
 //           overflow: hidden;
        }
        table {
            border-collapse: separate;
            border-spacing: 0 4px;
            table-layout: fixed;
        }

        tr {
            box-shadow: 2px 2px 2px dimgrey;
            background-color: blanchedalmond;
            cursor: pointer;
        }
        tr:hover {
            background-color: burlywood!important;
            box-shadow:none ;
        }
        td {
          padding: 0.5em 0.5em 0.5em 0.5em;

        }
        #cellInRow>div>div {
            margin: 0;
            padding: 0;
        }
        th {
            font-size: x-large;
            text-align: center;
            background-color: peru;
            border: solid 4px peru;
        }
        #flexcontainer {
            display: flex;
            flex-wrap:wrap-reverse;
        }
        #showEvent {
            width:100%;
            height: fit-content;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        #showEvent > *{
            margin: 0.5em 0 0.5em 0;
        }
        .bigBtn{
            background-color: blanchedalmond;
            width: 8em;
            height: 3em;
            margin-top:0.5em;
            padding: 0 2px 0 2px;
        }
        #participants {
            display: flex;
            justify-content: start;
            wrap-option: wrap;
        }
        #participants>div {
            padding: 0 0.5em 0 0.5em;
        }
        label {
            white-space: nowrap;
            display: inline-block;
        }

        @media (min-width: 768px) {
            table {
                width: 100%;
            }
            th {
                0.5em 0 0.5em 0;
            }
            #leftMenue {
                width: 20%;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                position: fixed;
                left: 0;
                top:3em;
            }
            #flexcontainer {
                justify-content: center;
            }
            #groups {
                width:20%;
                display: flex;
                position: fixed;
                top: 0;
                right: 0;
                flex-direction: column;
                justify-content: flex-start;
                padding-top: 3em;
            }
            #bottomMenue {
                display:none;
            }
            #main {
                height: 100%;
                width: 60%;
                text-align: center;
            }
        }
        @media (max-width: 767px) {
            table {
                table-layout: fixed;
                width: 100%;
            }
            th {
                padding 0 0 0.5em 0;
            }
            #flexcontainer {
                justify-content: center;
                align-items: flex-end;
                padding-bottom: 3em;
            }
            #groups {
                background-color: peru;
                padding-top: 8px;
                width: 100%;
                height: fit-content ;
                flex-direction: row;
                opacity: 1;
                flex-wrap: wrap;
            }
            #main {
                width: 98%;
                height: 100%;
                text-align: center;
                padding-top: 0.5em;
            }
            #leftMenue {
                display:none;
            }
            #bottomMenue {
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
<body onload="loadGroups();">

    <div id="leftMenue">
        <div style="width: 60%; margin:0 auto;">
            <a href="index.php?action=newevent"><button class ="bigBtn" style="width: 100%;">Event starten</button></a>
        </div>
        <div style="width:60%; margin:0 auto;">
            <button class ="bigBtn" style="width: 100%;" disabled>alle Events</button>
        </div>
        <div style="width: 60%; margin: 0 auto;">
            <a href="index.php?action=myaccount"><button class ="bigBtn"style="width: 100%;">Mein Account</button></a>
        </div>
    </div>
    <div id="flexcontainer">

        <div id="main">
            <table style="margin: 0 0;">
                <thead>
                <tr style="box-shadow: none; visibility: collapse;"> <th style="width:10%"></th><th style="width:12%"></th><th style="width: 39%"></th><th style="width: 39%"></th></tr>
                <tr style="box-shadow: none;"><th colspan="4">Kommende Veranstaltungen</th></tr></thead>
                <tbody id="eventlist">
                </tbody>
            </table>
        </div>
        <div id="groups">
        </div>
    </div>
    <div id="bottomMenue">
        <div style="width: 33.3%;">
            <a href="index.php?action=newevent"><button class ="bigBtn" style="width: 100%;">Event starten</button></a>
        </div>
        <div style="width:33.3%">
            <button class="bigBtn" style="width: 100%;" disabled>alle Events</button>
        </div>
        <div style="width: 33.3%;">
            <a href="index.php?action=myaccount"><button class="bigBtn" style="width: 100%;">Mein Account</button></a>
        </div>
    </div>
    <div id="store" style="display: none;">
        <div id='showEvent'">
            <h3 id="nameEvent"></h3>
            <div id="groupNames"></div>
            <div id="date"></div>
            <div id="ort"></div>
            <div><span  id="start"></span>
            <span id="end"></span></div>
            <div><span id='min'></span>
            <span id='max'></span></div>
            <div id='act'>angemeldet: </div>
            <div id="rate"></div>
            <div id='info' style="width:90%; white-space: pre-wrap; padding: 0.5em 0 0.5em 0; margin: 1em 0 1em 0;"></div>
            <div id="userIsIn"></div>
            <button type='button' id='optIn' class="bigBtn" style="background-color:floralwhite" onclick="addUserToEvent(event,this)">Ich mach mit</button>
            <button type="button" id="optOut" class="bigBtn" onclick="takeUserFromEvent(event,this)">Bin doch nicht dabei</button>
            <div id='participants'></div>
        </div>
    </div>

</body>
<script>
    const storedDiv =document.getElementById('store').innerHTML;
    let events = [];
    let groups = [];
    let groupIds = [];

    function compareTimes(eventDate, now){
        let diffinDays = Math.floor((eventDate-now)/(3600000*24)+1);
        if (diffinDays===0){
            return "heute";
        }
        if (diffinDays === 1){
            return "morgen";
        }
        if (diffinDays < 7){
            return "in "+diffinDays+" Tagen";
        }
        if (diffinDays > 13) {
            return "in " + Math.floor(diffinDays / 7) + " Wochen";
        }
        if (diffinDays >= 7 ) {
                return "in einer Woche";
        }


    }
    function buildTable(){
        let actrow = null;
        let haltmal ="";
        document.getElementById('eventlist').innerHTML="";
        let now = new Date(Date.now());
        let diffDateOld = "start";
        for (let singleEvent of events){
            let row = document.createElement('tr');
            let diffDateString = compareTimes(new Date(singleEvent['day']), now);
            if (diffDateString !== diffDateOld){
                row.style.boxShadow='none';
                row.innerHTML += "<td colspan='4' style='background-color: peru; text-align: center; white-space:pre'>"+
                    "&#x2193 &#x2193            "+diffDateString+"           &#x2193 &#x2193</td>";
                document.getElementById('eventlist').appendChild(row);
                row = document.createElement('tr');
                diffDateOld = diffDateString;
            }
            row.id= singleEvent['id'];
            row.value=singleEvent['userIsIn'];
            if (row.value===true){
                row.style.backgroundColor='navajowhite';
            }
            let start = singleEvent['start'].slice(0,5);
            row.style.backgroundImage='';
            row.style.opacity= '1';
            if (singleEvent['canceled']===true){
                row.style.backgroundImage="url('https://herter-dev.de/ressources/cancelled.png')";
                row.style.backgroundPosition= 'center';
                row.style.backgroundSize='contain';
            }  else if (singleEvent['actPers'] >= singleEvent['maxPers']){
                row.style.backgroundImage="url('https://herter-dev.de/ressources/completed.png')";
                row.style.backgroundPosition= '95% 50%';
                row.style.backgroundSize='100px';
                if (!row.value) {
                    row.style.opacity = '0.2';
                }
            }
            row.style.backgroundRepeat= 'no-repeat';
            row.innerHTML = "<td>"+getNiceDateFormat(singleEvent['day'], 'short')+"</td><td>"+start+"</td><td>"+singleEvent['type']+"</td><td>"+singleEvent['ort']+ "</td>";
            row.onclick = function() {
                singleEvent=loadSingleEvent(singleEvent['id']);
                if (singleEvent == "Event ist nicht vorhanden"){
                    getGroupsAndLoadEvent();
                    return;
                }
                if (document.getElementById('cellInRow') !== null) {
                    row.style.backgroundImage = null;
                    row.style.opacity= '1';
                    if (singleEvent['canceled']===true) {
                        row.style.backgroundImage = "url('https://herter-dev.de/ressources/cancelled.png')"
                        row.style.backgroundPosition = 'center';
                        row.style.backgroundSize = 'contain';
                    } else if (singleEvent['actPers'] >= singleEvent['maxPers']){
                        row.style.backgroundImage="url('https://herter-dev.de/ressources/completed.png')";
                        row.style.backgroundPosition= '95% 50%';
                        row.style.backgroundSize = '100px';
                        if (!this.value) {
                            row.style.opacity = '0.3';
                        }
                    } else document.getElementById('cellInRow').style.backgroundImage='';
                    row.style.backgroundRepeat = 'no-repeat';

                    document.getElementById('cellInRow').parentElement.innerHTML=haltmal;
                }
                if (this !== actrow) {
                    actrow = this;
                    haltmal = this.innerHTML;
                    this.innerHTML = "<td colspan='4'id='cellInRow' style='width: 380px;'>";
                    if (this.value === true) {
                        this.firstElementChild.style.backgroundColor = 'navajowhite';
                    } else {
                        this.firstElementChild.style.backgroundColor = 'blanchedalmond';
                    }
                    this.style.width = '100%';
                    document.getElementById('cellInRow').innerHTML = storedDiv;
                    singleEvent = loadSingleEvent(singleEvent['id']);
                    document.getElementById('nameEvent').innerHTML += singleEvent['type'];
                    let groupNames = "";
                    for (let groupName of singleEvent['group']) {
                        groupNames += groupName + ", ";
                    }
                    document.getElementById('optIn').disabled = false;
                    if (singleEvent['canceled']) {
                        document.getElementById('cellInRow').style.backgroundImage = "url('https://herter-dev.de/ressources/cancelled.png')";
                        document.getElementById('optIn').disabled = true;
                    } else if (singleEvent['actPers'] >= singleEvent['maxPers']) {
                        document.getElementById('cellInRow').style.backgroundImage = "url('https://herter-dev.de/ressources/completed.png')";
                        document.getElementById('optIn').disabled = true;
                    } else document.getElementById('cellInRow').style.backgroundImage = '';
                    document.getElementById('cellInRow').style.backgroundPosition = 'center';
                    document.getElementById('cellInRow').style.backgroundRepeat = 'no-repeat';
                    document.getElementById('cellInRow').style.backgroundSize = 'contain';

                    document.getElementById('groupNames').innerHTML += groupNames.slice(0, -2);
                    document.getElementById('ort').innerHTML += singleEvent['ort'];
                    document.getElementById('date').innerHTML += getNiceDateFormat(singleEvent['day'], 'long');
                    if (singleEvent['end'].slice(0, 5)!=="00:00") {
                        document.getElementById('start').innerHTML += start + " Uhr - ";
                        document.getElementById('end').innerHTML += singleEvent['end'].slice(0, 5) + " Uhr";
                    } else {
                        document.getElementById('start').innerHTML += "ab "+ start + " Uhr";
                    }

                    if (singleEvent['maxPers'] !== 9999) {
                        document.getElementById('min').innerHTML += singleEvent['minPers'];
                        document.getElementById('max').innerHTML = " - " + singleEvent['maxPers'];
                        document.getElementById('max').innerHTML += " Teilnehmer";
                    } else if (singleEvent['minPers'] != 0) {
                        document.getElementById('min').innerHTML = 'ab ' + singleEvent['minPers'];
                        document.getElementById('max').innerHTML += " Teilnehmer";
                    }
                    document.getElementById('act').innerHTML += singleEvent['actPers'];
                    if (singleEvent.rate !=0) {
                        document.getElementById('rate').innerHTML += "Alle " + singleEvent['rate'] + " Tage";
                    }
                    document.getElementById('info').innerHTML += singleEvent['info'];
                    loadTeilnehmer(singleEvent['id'],document.getElementById('participants'));
                    document.getElementById('optIn').value=singleEvent['id'];
                    if (this.value ===true) {
                        document.getElementById('optIn').style.display = 'none';
                        document.getElementById('optOut').style.display = 'block';
                    } else {
                        document.getElementById('optOut').style.display = 'none';
                        document.getElementById('optIn').style.display = 'block';
                    }
                } else {
                    actrow = null;
                }
            }
            document.getElementById('eventlist').appendChild(row);
        }
    }
    function loadTeilnehmer(eventId, elem){
        elem.innerHTML="";
        xhttp = new XMLHttpRequest();
        xhttp.onload = function () {
            if (xhttp.status === 200) {
                let teilnehmer = JSON.parse(xhttp.responseText);

                for (let person  of teilnehmer){
                    let personDiv = document.createElement('div');
                    personDiv.style.display='flex';
                    personDiv.style.flexDirection='column';
                    personDiv.style.alignItems='center';
                    personDiv.style.justifyContent='end';
                    let bild ="";
                    if (person[1] != null){
                        bild = "<img style='width:50px; height: 50px' src='"+person[1]+"'>";
                    } else {
                        bild ="";
                    }
                    personDiv.innerHTML =  bild + person[0];
                    elem.style.flexWrap = 'wrap';
                    elem.appendChild(personDiv);
                }
            } else {
                window.alert('Fehler beim Laden');
            }
        }
        xhttp.open('POST','index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send('action=loadTeilnehmer&eventId='+eventId);
    }

    function addUserToEvent(event,elem){
        event.stopPropagation();
        let eventId = elem.parentElement.parentElement.parentElement.id;
        xhttp = new XMLHttpRequest();
        xhttp.onload = function(){
            if (xhttp.status === 200) {
                if (xhttp.responseText === "ok") {
                    document.getElementById('optIn').style.display = 'none';
                    document.getElementById('optOut').style.display = 'block';
                    elem.parentElement.parentElement.style.backgroundColor='navajowhite';
                    elem.parentElement.parentElement.parentElement.style.backgroundColor='navajowhite';
                    document.getElementById(eventId).value = true;
                    document.getElementById('act').innerHTML = "angemeldet:"+loadSingleEvent(eventId)['actPers'];
                    loadTeilnehmer(eventId,document.getElementById('participants'));
                } else {
                    window.alert('Fehler!');
                }
            }
        }
        xhttp.open('POST','index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send("action=addUserToEvent&event="+eventId);
    }
    function takeUserFromEvent(event,elem){
        NodeList.prototype.indexOf = Array.prototype.indexOf;
        event.stopPropagation();
        let eventId = elem.parentElement.parentElement.parentElement.id;
        xhttp = new XMLHttpRequest();
        xhttp.onload = function(){
            if (xhttp.status === 200) {
                if (xhttp.responseText === "Event deleted") {
                    getGroupsAndLoadEvent();
                } else {
                    let event = loadSingleEvent(eventId);
                    if (event['canceled']) {
                        document.getElementById('cellInRow').style.backgroundImage = "url('https://herter-dev.de/ressources/cancelled.png')";
                        document.getElementById('cellInRow').style.backgroundPosition = 'center';
                        document.getElementById('cellInRow').style.backgroundRepeat = 'no-repeat';
                        document.getElementById('cellInRow').style.backgroundSize = 'contain';
                        document.getElementById('optIn').disabled = true;
                    }
                    document.getElementById('optOut').style.display = 'none';
                    document.getElementById('optIn').style.display = 'block';
                    elem.parentElement.parentElement.style.backgroundColor = 'blanchedalmond';
                    elem.parentElement.parentElement.parentElement.style.backgroundColor = 'blanchedalmond';
                    document.getElementById('act').innerHTML = "angemeldet:" + event['actPers'];
                    document.getElementById(eventId).value = false;
                    loadTeilnehmer(eventId, document.getElementById('participants'));
                }
            }
        }
        xhttp.open('POST','index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send("action=takeUserFromEvent&event="+eventId);
    }
    function loadSingleEvent(id){
        let singleEvent=null;
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function(){
            if (xhttp.status === 200){
                if (xhttp.responseText !="Event ist nicht vorhanden") {
                    singleEvent = JSON.parse(xhttp.responseText);
                } else singleEvent = xhttp.responseText;
            }
        }
        xhttp.open('POST','index.php',false);
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send('action=loadSingleEvent&eventId='+id);
        return singleEvent;
    }

    function getNiceDateFormat(uglyDate, version) {
        const month = ['Januar', 'Februar','März','April','Mai','Juni','Juli', 'August', 'September','Oktober','November','Dezember'];
        const week =['So','Mo', 'Di', 'Mi', 'Do', 'Fr','Sa'];
        let day = new Date(uglyDate);
        if (version ==='long') {
            return week[day.getDay()] + ", " + day.getDate() + ". " + month[day.getMonth()] + " " + day.getFullYear();
        } else {
            return week[day.getDay()]+ "<br> " + day.getDate() + "." + (1 + day.getMonth()).toString();
        }


    }
    function loadEvents(groupIds, mine=null){

        let xhttp = new XMLHttpRequest();
        xhttp.onload = function () {
            if (xhttp.status === 200) {
                events = JSON.parse(xhttp.responseText);
                buildTable();
            }
        }
        xhttp.open('POST', 'index.php', true);
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        if (mine===true){
            xhttp.send("action=loadEvents&events=mine&groupIds="+JSON.stringify(groupIds));
        }else {
            xhttp.send("action=loadEvents&groupIds=" + JSON.stringify(groupIds));
        }
    }
    function loadGroups(){
        let xhttp = new XMLHttpRequest();
        xhttp.onload = function () {
            if (xhttp.status === 200) {
                groups = JSON.parse(xhttp.responseText);
                buildGroupSelect();
                getGroupsAndLoadEvent();
            }
        }
        xhttp.open('POST', 'index.php');
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send("action=loadGroups&groupView=1");

    }
    function getGroupsAndLoadEvent(){
        let groupIds=[];
        for (let elem of document.getElementsByClassName('groupCheck')){
            if (elem.checked){
                groupIds.push(elem.value);
            }
        }
        if (document.getElementById('mine').checked){
            loadEvents(groupIds, true);
        } else {
            loadEvents(groupIds);
        }
    }
    function buildGroupSelect(){
        for (let group of groups) {
            document.getElementById('groups').innerHTML +=
                "<label style='margin:1em 1em 0 1em'><input type='checkbox' class='groupCheck' value='"
                + group['id'] + "' checked> " + group['name'] + "</input></label>";
        }
        document.getElementById('groups').innerHTML +=
            "<br><label style='margin:1.5em 1em 0 1em'; ><input class='groupCheck' type='checkbox' id = 'mine'>Nur meine Events zeigen</input></label>";
        for (let elem of document.getElementsByClassName('groupCheck')){
            elem.onchange = getGroupsAndLoadEvent;
        }
    }
</script>
</html>
<?php
