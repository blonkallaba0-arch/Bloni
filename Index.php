<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Happy Birthday 🎉</title>

<style>
body {
margin: 0;
background: linear-gradient(135deg, #ff4b7d, #ff758c);
color: white;
font-family: Arial, sans-serif;
text-align: center;
overflow: hidden;
}

/* Countdown */
#countdown {
font-size: 80px;
margin-top: 40%;
font-weight: bold;
animation: fade 1s ease;
}

/* Main content */
#main {
display: none;
padding: 20px;
}

h1 {
font-size: 28px;
margin-top: 20px;
}

/* Image */
img {
width: 80%;
max-width: 300px;
height: auto;
border-radius: 20px;
margin-top: 20px;
box-shadow: 0 0 20px rgba(0,0,0,0.4);
}

/* Message */
p {
font-size: 18px;
margin-top: 10px;
}

/* Fade animation */
@keyframes fade {
from {opacity: 0;}
to {opacity: 1;}
}
</style>

</head>

<body>

<div id="countdown">3</div>

<div id="main">
<h1>🎉 Happy Birthday 🎉</h1>
<p>Urime ditëlindja! 🎂 Je shumë i/e veçantë ❤️</p>
<img id="photo" src="foto1.jpg">
</div>

<audio id="music" loop>
<source src="music.mp3" type="audio/mpeg">
</audio>

<script>
let count = 3;
let countdownEl = document.getElementById("countdown");
let main = document.getElementById("main");
let music = document.getElementById("music");

/* Countdown */
let timer = setInterval(() => {
count--;
if (count > 0) {
countdownEl.innerHTML = count;
} else {
countdownEl.innerHTML = "🎉";
clearInterval(timer);

setTimeout(() => {
countdownEl.style.display = "none";
main.style.display = "block";
music.play();
startSlideshow();
}, 800);
}
}, 1000);

/* Slideshow */
let photos = ["foto1.jpg", "foto2.jpg", "foto3.jpg"];
let i = 0;

function startSlideshow() {
setInterval(() => {
i = (i + 1) % photos.length;
document.getElementById("photo").src = photos[i];
}, 2500);
}
</script>

</body>
</html>
