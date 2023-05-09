# Events

add a config.php in app/ with  

const DB_SERVER= '' ;  
const DB_USER= '' ;  
const DB_PASSWD= '' ;  
const DB_NAME=  '' ;  
const new_DB= false  ;  // set to true if you don't have a database. **True will delete an existing database and create a new one. so be carfull!**

// the following part is for sending an email if you forgot your password. For testing porpose you can leave this blank.  
const mail_UN = '';  
const mail_PW = '';  
const WEBADRESSE = '';  
const MAIL_HOST = '';  
const MAIL_ADRESS_PASS = '';  
const MAIL_ADRESS_LINK = '';  
const SMTP_PORT = 587; 


start with docker compose up -d  
  
app listens on Port 80. So you can test the app on http://127.0.0.1  
