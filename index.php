<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <script src="https://www.WebRTC-Experiment.com/RecordRTC.js"></script>
  <script defer src="node_modules/@fortawesome/fontawesome-free/js/brands.js" type="application/javascript"></script>
  <script defer src="node_modules/@fortawesome/fontawesome-free/js/solid.js" type="application/javascript"></script>
  <script defer src="node_modules/@fortawesome/fontawesome-free/js/fontawesome.js" type="application/javascript"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</head>


<style>
  body {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
    text-align: center;
  }

  #prediction {
    width: 90%;
    margin: 1%;
    font-size: 170%;
    opacity: .7;
  }

  #details {
    padding: 1%;
    opacity: .7;
    margin: 1%;
  }

  .hidden {
    display: None;
  }

  .button {
    background: rgba(0, 0, 0, 0);
    border: none;
    padding: 1%;
    border-radius: 5px;
    transition-duration: .3s;
    margin: 1%;
  }

  .btn:hover {
    background: rgba(0, 0, 0, .1);
  }

  .buttons {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    height: 50%;
  }

  #details {
    height: 0%;
    transition-duration: 0.5s;
  }

  .tall {
    height: 100% !important;
  }
</style>

<body>
  <div id="prediction"> <?php
                        if ($_GET["transcribed"]) {
                          echo $_GET["transcribed"];
                        } else {
                          echo "Text";
                        }
                        ?></div>
  <form id="form" method=post action=/transcribe.php>
    <input id="file" type=hidden name=file value=""></input>
    <input id="language_form" type=hidden name=language value=""></input>
  </form>
  <div id="details" class="hidden">
    <table>
      <tr>
        <td id="confidence"></td>
      </tr>
    </table>
  </div sytle="width: 50%">
  <button type="button" class="button hidden" id="stop"><i class="fas fa-stop fa-lg"></i></button>
  <button type="button" class="button" id="start"><i class="fas fa-play fa-lg"></i></button>
  <button type="button" class="button" id="detail"><i class="fas fa-info-circle fg-lg"></i></button>

  <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
    <input type="radio" class="btn-check" name="language" id="en" autocomplete="off" value="en" checked>
    <label class="btn btn-outline-secondary" for="en">English</label>

    <input type="radio" class="btn-check" name="language" id="es" autocomplete="off" value="es">
    <label class="btn btn-outline-secondary" for="es">Español</label>
  </div>

</body>
<!--<script src="node_modules/socket.io/client-dist/socket.io.js"></script>-->
<!--<script type="module" src="env.js" type="text/javascript"></script>-->
<script type="module">
  //import PATH from './env.js'
  //var socket = io("https://aholab.ehu.eus/", { path: PATH })

  const stopButton = document.getElementById("stop");
  const startButton = document.getElementById("start");
  const detailButton = document.getElementById("detail");
  const detailsDiv = document.getElementById("details");
  const laguageInputs = document.getElementsByClassName("btn-check")
  0

  function getCurrentLanguage() {
    var radios = document.getElementsByName('language');
    for (var i = 0, length = radios.length; i < length; i++) {
      if (radios[i].checked) {
        return radios[i].value
      }
    }
  }

  detailButton.addEventListener("click", () => {
    detailsDiv.classList.toggle("hidden")
  })

  const handleSuccess = async function(stream) {
    const mediaRecorder = await new RecordRTC(stream, {
      type: 'audio',
      recorderType: RecordRTC.StereoAudioRecorder, // force for all browsers
      numberOfAudioChannels: 2
    });
    mediaRecorder.startRecording()
    stopButton.classList.remove("hidden")


    const functionOnClick = async function() {
      stopButton.removeEventListener("click", functionOnClick)
      stopButton.classList.add("hidden")
      await mediaRecorder.stopRecording(async function() {
        let blob = mediaRecorder.getBlob();
        const language = getCurrentLanguage()
        console.log("blob: ", await blob.text(), await blob.arrayBuffer())
        var fd = new FormData()
        fd.append("content", blob)

        //socket.emit("file", {blob, language})
        /*
        let body = await fetch("./transcribe.php/", {
          method: "POST",
          body: JSON.stringify({file: await blob.text(), language})
        })
        
        */

        var reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = function() {
          var base64data = reader.result;
          console.log(base64data);
          document.getElementById("language_form").value = language;

          document.getElementById("file").value = base64data;

          //document.getElementById("file").value = fd;

          document.getElementById("form").submit();

          document.getElementById("prediction").innerText = "Hello"
          document.getElementById("confidence").innerText = `Confidence: ${confidence}`
        }
      });
      //startButton.classList.remove("hidden")
    }

    stopButton.addEventListener("click", functionOnClick);
  };

  startButton.addEventListener("click", () => {
    document.getElementById("prediction").innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>'
    startButton.classList.add("hidden")
    navigator.mediaDevices
      .getUserMedia({
        audio: true,
        video: false
      })
      .then(handleSuccess);
  })

  /*
  socket.on("prediction", function ({ text, confidence }) {
    console.log("result: ", text)
    document.getElementById("prediction").innerText = text
    document.getElementById("confidence").innerText = `Confidence: ${confidence}`
  })
  */
</script>

</html>