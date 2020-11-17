<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="http://www.dpo.go.th/wp-content/uploads/2016/06/cropped-cow-32x32.png" sizes="32x32">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
   <title>Import Excel To Database Send Data MiS</title>
   <style>
  body
  {
   margin:0;
   padding:0;
   background-color:#f1f1f1;
  }
  .box
  {
   width:700px;
   border:1px solid #ccc;
   background-color:#fff;
   border-radius:5px;
   margin-top:100px;
  }
  button a{
    color:#fff;
  }
  
/*   body { */
/*   background:#eee; */
/*   padding: 30px; */
/*   font-size: 62.5%; */
/*   font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; */
/*   position: relative; */
/*   margin: 0; */
/* } */
  
/* #container { */
/*   margin: 0 auto; */
/*   width: 460px; */
/*   padding: 2em; */
/*   background: #DCDDDF; */
  
/* } */
  
.ui-progress-bar {
  margin-top: 3em;
  margin-bottom: 3em;
}
  
.ui-progress span.ui-label {
  font-size: 1.2em;
  position: absolute;
  right: 0;
  line-height: 33px;
  padding-right: 12px;
  color: rgba(0,0,0,0.6);
  text-shadow: rgba(255,255,255, 0.45) 0 1px 0px;
  white-space: nowrap;
}@-webkit-keyframes animate-stripes {
  from {
    background-position: 0 0;
  }
  
  to {
   background-position: 44px 0;
  }
}    
.progress .progress-bar {
    -moz-animation-name: animateBar;
    -moz-animation-iteration-count: 1;
    -moz-animation-timing-function: ease-in;
    -moz-animation-duration: .4s;

    -webkit-animation-name: animateBar;
    -webkit-animation-iteration-count: 1;
    -webkit-animation-timing-function: ease-in;
    -webkit-animation-duration: .4s;

    animation-name: animateBar;
    animation-iteration-count: 1;
    animation-timing-function: ease-in;
    animation-duration: .4s;
}

@-moz-keyframes animateBar {
    0% {-moz-transform: translateX(-100%);}
    100% {-moz-transform: translateX(0);}
}
@-webkit-keyframes animateBar {
    0% {-webkit-transform: translateX(-100%);}
    100% {-webkit-transform: translateX(0);}
}
@keyframes animateBar {
    0% {transform: translateX(-100%);}
    100% {transform: translateX(0);}
}
.progress {
    background-color: #BCBEBF;
    text-align: left;
    position: relative;
    height: 13px;
    margin: 8px 8px 8px 8px;
}
.progress-bar {
    background-color: #323232;
    text-align: left;
    line-height: 13px;
    padding: 1px 10px 2px;
}
.progress-bar span {
    padding: 1px 10px 2px;
    position: absolute;
    z-index: 2;
    color: white;
    top: 50%;
    left: 0%;
    transform: translate(0%,-50%);
}
table{
  width:100%;
}
  </style>
  </head>
    <body>
    <div class="jumbotron text-center blue-grey lighten-5">
    <center>
     <img src="logo.png" style="width:100px;" />
     </center>