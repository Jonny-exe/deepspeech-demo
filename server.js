const express = require("express");
const fs = require("fs");
const app = express();
const http = require("http");
const httpServer = http.createServer(app);
const { Server } = require("socket.io");
const { exec } = require("child_process");
const io = require("socket.io")(httpServer, {
  cors: {
    origins: ["*"],
  },
});

const PATH_MODEL = "models/output_graph.pbmm";
const PATH_AUDIO = "test.wav";
const CONDA_ENV_NAME = "deepspeech";

app.get("/", (req, res) => {
  res.sendFile(__dirname + "/index.html");
});

io.on("connection", (socket) => {
  console.log("a user connected");

  socket.on("file", async (blob) => {
    console.log(blob);
    writeToFile(blob);
    var result = await callDeepspeech();
    console.log(result);
    socket.emit("prediction", result);
  });

  socket.on("disconnect", () => {
    console.log("user disconnected");
  });
});

httpServer.listen(3000, () => {
  console.log("listening on *:3000");
});

function writeToFile(blob) {
  fs.writeFileSync("test.wav", blob);
}

async function callDeepspeech() {
  const command = `conda run -n ${CONDA_ENV_NAME} deepspeech --model ${PATH_MODEL} --audio ${PATH_AUDIO} --json`;
  // TODO: add tests to see if deepspeech is installed
  var result = await execCommand(command);
  return JSON.parse(result);
}

function execCommand(command, callback) {
  return new Promise(function (resolve, reject) {
    exec(command, (_error, stdout, _stderr) => {
      resolve(stdout);
    });
  });
}
