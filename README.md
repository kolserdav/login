**Login module**  
The login module is designed to use Javascript with Ajax, since server responses are produced in JSON format as:  
```javascript
JSON.parse({
"response" : "server response text",
"result" : "boolean",
"code" : "num of code"
});
 ```   
 
 _Installation_          
 
 $~ composer require kolserdav/login
 
 _Dependencies_    
 
 `
 "kolserdav/dbconnect": "^2.0", #https://github.com/kolserdav/dbconnect  
 `
 `
 "kolserdav/router": "^0.2.1",  #https://github.com/kolserdav/router  
 `
 `  
 "phpmailer/phpmailer": "^6.0"	#https://github.com/phpmailer/phpmailer  
`

_Using for registration_



