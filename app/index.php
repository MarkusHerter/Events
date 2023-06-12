<?php

session_start();
include '.config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
spl_autoload_register(function ($className) {include 'Classes/' . $className . '.php';});
if (new_DB){
    Db::createDb();
}
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

$action = $_REQUEST['action'] ?? "showLogin";
if (isset ($_REQUEST['logout'])){
    session_destroy();
}
if (!in_array($action,['login', 'register', 'showRegister','sendmail'])) {
    $action = isset($_SESSION['id']) ? $action : "showLogin";
}
$view = $_REQUEST['view'] ?? "";
if (isset($_REQUEST['gr']) && $_REQUEST['gp']){
    $_SESSION['gr']=$_REQUEST['gr'];
    $_SESSION['gp']=$_REQUEST['gp'];
}
if (isset ($_REQUEST['code'])){
    $_SESSION['code']=$_REQUEST['code'];

}
switch ($action) {
    case ('myaccount'):
        $view = 'myaccount';
        break;
    case ('eventlist'):
        $view='eventlist';
        break;
    case('newevent'):
        $view='newevent';
        break;
    case ('showLogin'):
        $view = 'login';
        break;
    case ('showRegister'):
        $view = 'register';
        break;
    case ('register'):
        $user = new User ($_POST['name'], $_POST['email'],$_POST['passwd']);
        try {
            $user->save();
            $_SESSION['id']=$user->getId();
            $_SESSION['name']=$_POST['name'];
            einladung();
            if (!Group::loadGroupsByUserId($user->getId())){
                $glow = true;
                $view ='myaccount';
            } else $view = 'eventlist';
        } catch (\Exception $e){
            $view = 'register';
        }
        break;
    case ('login'):
        try {
            $id = User::getIdByEmailAndPasswd($_POST['email'],$_POST['passwd']);
            $_SESSION['id']=$id;
            $_SESSION['name']=User::getNameById($id);
            einladung();
            if (!Group::loadGroupsByUserId($_SESSION['id'])){
                $glow = true;
                $view ='myaccount';
            } else $view = 'eventlist';
        } catch (\Exception $e){
            $view = 'login';
        }
        break;
    case ('joinGroup'):
        $groupId = Group::loadIdByNameAndPass($_REQUEST['name'], $_REQUEST['passwd']);
        if ($groupId !== 0){
            echo($groupId);
            $group = Group::loadById($groupId);
            if ($group->privacy === 0 || $group->privacy===2) {
                $status = UserToGroups::save($_SESSION['id'], $groupId);
            }
            else if ($group->privacy === 1 || $group->privacy === 3){
                if (isset($_REQUEST['code']) && $group->checkInvitation($_REQUEST['code'])) {
                    $status = UserToGroups::save($_SESSION['id'], $groupId);
                } else {
                    die ('Du bist nicht eingeladen');
                }
            }
            if ($status) {
                die('ok');
            } else {
                die('Du bist bereits in der Gruppe!');
            }
        }else {
            die('Gruppe nicht vorhanden');
        }
    case ('saveNewGroup'):
        function newRandString($length = 9):string
        {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMOPQRSTUVWXYZ';
            $charsLength = strlen($chars);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $chars[rand(0, $charsLength - 1)];
            }
            return $randomString;
        }
        if (isset($_REQUEST['name']) && strlen($_REQUEST['name']) >0) {
            do {
                $group = NEW Group($_REQUEST['name'], newRandString(),$_REQUEST['privacy'],$_SESSION['id']);
                $success = $group->save();
            } while ($success === false);
            UserToGroups::save($_SESSION['id'], $group->id);
            die($_REQUEST['privacy']);
        } else {
            die('Name ist ungültig');
        }
    case ('getInvitationCode'):
        $group = Group::loadById($_REQUEST['id']);
        if ($group->owner == $_SESSION['id']) {
            die (Group::createInvitationCode($_REQUEST['id']));
        } else {
            die ();
        }
    case ('leaveGroup'):
        UserToGroups::delete($_SESSION['id'],$_REQUEST['groupId']);
        $eventIds = UserToEvents::getEventIdsByUserId($_SESSION['id']); //nimm alle Events des Users
        foreach ($eventIds as $eventId){
            $allGroupsOfEvent = EventsToGroups::getGroupIdByEvent($eventId); // für jedes Event, nimm dessen Gruppen
            if (in_array($_REQUEST['groupId'],$allGroupsOfEvent)){ //Wenn die Gruppe drin ist, aus der der User raus ist,
                UserToEvents::delete($_SESSION['id'],$eventId); //lösche die Verbindung des Users zum Event
                $event = Event::loadById($eventId);
                if ($event->canceled===true && $event->actPers===0){
                    $event->delete();
                    if (Event::loadById($eventId) === null) {
                        EventsToGroups::delete('e', $eventId);
                    }
                }
            }
        }
        if (!UserToGroups::GroupIsIn($_REQUEST['groupId'])){
            Group::deleteGroup($_REQUEST['groupId']);
        }
        die();
    case ('loadGroups'):
        $allGroups =Group::loadGroupsByUserId($_SESSION['id']);
        $allGroups2 = [];
        if (isset($_REQUEST['groupView'])){
            die(json_encode($allGroups));
        } else {
            foreach ($allGroups as $group) {
                if ($group->privacy < 2 || $group->owner === $_SESSION['id']) {
                    $allGroups2[] = $group;
                }
            }
        }
        die(json_encode($allGroups2));
    case('loadSingleEvent'):
        $groups = EventsToGroups::getGroupIdByEvent($_REQUEST['eventId']);
        $groupNames =[];
        foreach ($groups as $group){
            $groupNames[] = Group::loadById($group)->name;
        }
        try {
            $event = Event::loadById($_REQUEST['eventId']);
            $event->addGroup($groupNames);
            die (json_encode($event));
        } catch (Exception $e){
            die($e->getMessage());
        }
    case ('loadEvents'):
        $groupIds = json_decode($_REQUEST['groupIds']);
        $eventIds = [];
        foreach ($groupIds as $groupId) {
            if (UserToGroups::UserIsInGroup($_SESSION['id'],$groupId)) {
                $eventIds = array_merge($eventIds, EventsToGroups::loadEventIds((int)$groupId));
            }
        }
        $eventIds = array_unique($eventIds);
        if (isset($_REQUEST['events']) && $_REQUEST['events']==='mine'){
            $newEventIds = $eventIds;
            $eventIds =[];
            foreach ($newEventIds as $id){
                if (UserToEvents::userIsIn($_SESSION['id'], $id)){
                    $eventIds[]=$id;
                }
            }
        }
        $allEvents = [];
        foreach ($eventIds as $id) {
            if($event = Event::loadById($id)) {
                $event->userIsIn = UserToEvents::userIsIn($_SESSION['id'], $id);
                $allEvents[] = $event;
            }
        }
        usort($allEvents, function ($a, $b)
        {
            if ($a->day == $b->day) {
                if ($a->start == $b->start){
                    return 0;
                }
                return ($a->start < $b->start) ? -1: 1;
            }
            return ($a->day < $b->day) ? -1 : 1;
        });
        die((json_encode($allEvents)));
    case ('saveEvent'):
        $_REQUEST['minPers'] = $_REQUEST['minPers']==='' ? 0 : $_REQUEST['minPers'];
        $_REQUEST['maxPers'] = $_REQUEST['maxPers']==='' ? 9999 : $_REQUEST['maxPers'];
        $event = new Event(urldecode($_REQUEST['nameEvent']),$_REQUEST['date'],urldecode($_REQUEST['startTime']),urldecode($_REQUEST['endTime']),$_REQUEST['minPers'],$_REQUEST['maxPers'], $_REQUEST['ort'], $_REQUEST['info']??'', (int)$_SESSION['id'], $_REQUEST['rate'],false);
        $eventId = $event->saveEvent();
        UserToEvents::save($_SESSION['id'],$eventId);
        foreach ($_REQUEST['groups'] as $groupId){
            if (UserToGroups::UserIsInGroup($_SESSION['id'], $groupId)) {
                $group = Group::loadById($groupId);
                if ($group->privacy < 2 || $group->owner === $_SESSION['id']) {
                    EventsToGroups::save($eventId, $groupId);
                }
            }
        }
        die('ok');
    case ('loadIniEvents'):
        $allEvents = Event::loadAllByStarterId($_SESSION['id']);
        usort($allEvents, function ($a, $b)
        {
            if ($a->day == $b->day) {
                if ($a->start == $b->start){
                    return 0;
                }
                return ($a->start < $b->start) ? -1: 1;
            }
            return ($a->day < $b->day) ? -1 : 1;
        });
        die(json_encode($allEvents));
    case('deleteEvent'):
        if (Event::isStarter($_REQUEST['eventId'],$_SESSION['id'])){
           UserToEvents::delete($_SESSION['id'], $_REQUEST['eventId']);
           $usersInEvent =UserToEvents::getUserIdsByEventId($_REQUEST['eventId']);
           if (isset($usersInEvent[0])) {
               Event::setCanceled($_REQUEST['eventId']);
               die('canceled');
           } else {
               Event::loadById($_REQUEST['eventId'])->delete();
               die('deleted');
           }
        }
    case ('loadTeilnehmer'):
        $userNames =[];
        if (isset($_REQUEST['eventId'])) {
            $groupIds = EventsToGroups::getGroupIdByEvent($_REQUEST['eventId']);
            foreach ($groupIds as $groupId){
                if (UserToGroups::UserIsInGroup($_SESSION['id'],$groupId)){
                    $userIds = UserToEvents::getUserIdsByEventId($_REQUEST['eventId']);
                    break;
                }
            }
        } else if (isset($_REQUEST['groupId'])){
            if (UserToGroups::UserIsInGroup($_SESSION, $_REQUEST['groupId'])) {
                $userIds = UserToGroups::getUserIdsByGroup($_REQUEST['groupId']);
            }
        }
        foreach ($userIds as $id){
            $userNames[] = User::getNameAndPicById($id);
        }
        die (json_encode($userNames));
    case ('savePic'):
        if (isset($_FILES['pic'])){
            User::savePicInUser($_SESSION['id'],$_FILES['pic']['tmp_name']);
            die('Bild gespeichert');
        } else {
            die ('keine Daten angekommen');
        }
    case ('loadUserPic'):
        die(json_encode(User::getNameAndPicById($_SESSION['id'])));
    case ('addUserToEvent'):
        $groupsOfEvent = EventsToGroups::getGroupIdByEvent($_REQUEST['event']);
        $allowed = false;
        foreach ($groupsOfEvent as $groupId) {
            if (UserToGroups::UserIsInGroup($_SESSION['id'],$groupId)){
                $allowed = true;
                break;
            };
        }
        UserToEvents::save($_SESSION['id'],$_REQUEST['event']);
        die('ok');
    case ('takeUserFromEvent'):
        UserToEvents::delete($_SESSION['id'],$_REQUEST['event']);
        $event = Event::loadById($_REQUEST['event']);
        if ($event->canceled===true && $event->actPers===0){
            $event->delete();
            if (Event::loadById($_REQUEST['event']) === null) {
                EventsToGroups::delete('e',$_REQUEST['event']);
                die('Event deleted');
            }
        }
        die('ok');
    case ('changePassword'):
        if (User::isCorrectPassword($_SESSION['id'], $_REQUEST['passwdOld'])){
            try {
                User::changePassword($_SESSION['id'], $_REQUEST['passwdNew']);
                die ('Passwort geändert');
            } catch (\Exception $e){
                die ($e->getMessage());
            }
        }
        die ('Passwort ist nicht korrekt.');
    case('sendInvitation'):
        if (!filter_var($_REQUEST['email'],FILTER_VALIDATE_EMAIL)) {
            die ("Email-Adresse ist ungültig.");
        }
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = mail_UN;
        $mail->Password = mail_PW;
        $mail->SMTPSecure = 'SSL';
        $mail->Port = SMTP_PORT;
        $mail->setFrom(MAIL_ADRESS_LINK, 'Schnullibutz');
        $mail->addAddress($_POST['email']);
        $mail->Subject = 'Events.online Einladung';
        $mail->isHTML(true);
        $mailString =WEBADRESSE."?gr=".urlencode($_REQUEST['gr'])."&gp=".$_REQUEST['gp'];
        if (isset($_REQUEST['code'])){
            $mailString .= "&code=".$_REQUEST['code'];
        }
        $mailContent = "<div style='display:flex; flex-direction: column; align-items: center;'><h1>Send HTML Email using SMTP in PHP</h1>".
            "<p >Dein Einladungslink: </p><p style=' font-size: x-large;'>".$mailString."</p></div>";
        $mail->Body = $mailContent;
        if(!$mail->send()){
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }else {
            echo 'Message has been sent';
        }
        die();
    case ('sendmail'):
        if (User::emailExists($_POST['email'])){
            function newRandString($length = 9):string
            {
                $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!§$%&/()=?+#*;:--';
                $charsLength = strlen($chars);
                $randomString = '';
                for ($i = 0; $i < $length; $i++) {
                    $randomString .= $chars[rand(0, $charsLength - 1)];
                }
                return $randomString;
            }
            $newPasswd = newRandString();
                User::newPassword($_POST['email'],$newPasswd);
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = mail_UN;
            $mail->Password = mail_PW;
            $mail->SMTPSecure = 'SSL';
            $mail->Port = SMTP_PORT;
            $mail->setFrom(MAIL_ADRESS_PASS, 'Markus');
            //$mail->addReplyTo('info@mailtrap.io', 'Mailtrap';
            $mail->addAddress($_POST['email']);
            $mail->Subject = 'Test Email using PHPMailer';
            $mail->isHTML(true);
            $mailContent = "<div style='display:flex; flex-direction: column; align-items: center;'><h1>Send HTML Email using SMTP in PHP</h1>".
                "<p >Neues Passwort: </p><p style=' font-size: x-large;'>".$newPasswd."</p></div>";
            $mail->Body = $mailContent;
            if(!$mail->send()){
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            }else {
                echo 'Message has been sent';
            }
            $view="login";
        } else {
            echo ("Email-Adresse existiert nicht");
            $view ="newpass";
        }
        break;
    case ('change'):
        try {
            if ($_REQUEST['rate'] == 0)
            {
                Event::setRateByEventId($_REQUEST['event'],0);
            }
            Event::addInfo($_REQUEST['event'], $_REQUEST['info']);
        } catch (\Exception $e){
            echo "Fehler!";
        }
        die("ok");
}

include $view.'.php';

function einladung(){
    if (isset($_SESSION['gr'])){
        $groupId = Group::loadIdByNameAndPass($_SESSION['gr'], $_SESSION['gp']);
        if ($groupId !== 0){
            $group = Group::loadById($groupId);
            if ($group->privacy === 0) {
                $status = UserToGroups::save($_SESSION['id'], $groupId);
            }
            if ($group->privacy === 1){
                if ($group->checkInvitation($_SESSION['code'])){
                    $status = UserToGroups::save($_SESSION['id'], $groupId);
                } else {
                    echo ('Der Einladungscode ist falsch oder nicht mehr gültig');
                    $_SESSION['gr'] = null;
                    $_SESSION['gp'] = null;
                    $_SESSION['code'] = null;
                    return 0;
                }
            }
            if ($status){
                echo('Die bist jetzt in der Gruppe '.$_SESSION['gr']);
            } else {
                echo('Du bist bereits in der Gruppe '.$_SESSION['gr']);
            }
        }else {
            die('Gruppe'.$_SESSION['gr'].' nicht vorhanden');
        }
        $_SESSION['gr'] = null;
        $_SESSION['gp'] = null;
    }
}