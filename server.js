const express = require("express");
const app = express();
const http = require("http");
const server = http.createServer(app);
const { Server } = require("socket.io");
const io = new Server(server, {
  cors: {
    origins: ["*"],
  },
  // path: "/"
});


const { exec } = require("child_process");
const readline = require('readline');
const fs = require("fs");

app.use(express.json())
console.log(io.path())

const PATH_MODEL = "models/output_graph.pbmm";
const PATH_AUDIO = "test.wav";
const CONDA_ENV_NAME = "deepspeech";

app.get("/", (req, res) => {
  console.log("Test")
  res.sendFile(__dirname + "/index.html");
});

app.get("/api", (req, res) => {
  res.sendFile(__dirname + "/api.html")
})

app.get("/dev/api_paths", (req, res) => {
  res.send(app._router.stack)
})

app.post("/dev/api/file", async (req, res) => {
  const path = req.body?.path_file
  console.log("path", path)
  if (path === undefined || fs.existsSync(path) == false) {
    console.log("Undefined")
    res.sendStatus(400)
    return
  }

  var result = await callDeepspeech(path);
  res.send(result)
})

app.post("/dev/api/multiple_file", async (req, res) => {
  const path = req.body?.path_list
  console.log("path", path)
  if (path === undefined || fs.existsSync(path) == false) {
    console.log("Undefined")
    res.sendStatus(400)
    return
  }

  var rd = readline.createInterface({
    input: fs.createReadStream('/path/to/file'),
    output: process.stdout,
    console: false
  });

  rd.on('line', function (line) {
    console.log(line);
  });

  var result = await callDeepspeech(path);
  res.send(result)
})

server.listen(2002, () => {
  console.log("listening on *:2002");
  console.log("http://%s:%s", server.address().address, server.address().port);
});

io.on("connection", (socket) => {
  console.log("a user connected");

  socket.on("file", async (blob) => {
    console.log(blob);
    writeToFile(blob, PATH_AUDIO);
    var result = await callDeepspeech(PATH_AUDIO);
    console.log(result);
    socket.emit("prediction", result);
  });

  socket.on("disconnect", () => {
    console.log("user disconnected");
  });
});

function writeToFile(blob, path) {
  fs.writeFileSync(path, blob);
}

async function callDeepspeech(path_audio) {
  const command = `conda run -n ${CONDA_ENV_NAME} deepspeech --model ${PATH_MODEL} --audio ${path_audio} --json`;
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
