
require('dotenv').config()
const express = require("express");
const pm2 = require("./pm2-control");
const app = express();

var bodyParser = require("body-parser");
app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());

var HTTP_PORT = process.env.PM2_PORT || 10000;

// Start server
app.listen(HTTP_PORT, () => {
    console.log("Server running on port %PORT%".replace("%PORT%", HTTP_PORT));
});

app.get("/", async function(req, res, next){
    const list = await pm2.list();
    res.json({
        code: 200,
        message: "Ok",
        data: list,
    });
});

app.post("/trigger", async function(req, res, next){
    const worker_name = req.body.worker_name ? req.body.worker_name : '';
    const action = req.body.action ? req.body.action : '';

    if(!worker_name) {
      return res.status(400).json({ code: 'params.worker_name.required'});
    }
    if(!['restart', 'start', 'stop', 'delete'].includes(action)) {
      return res.status(400).json({ code: 'params.action.invalid'});
    }

    switch (action) {
      case 'restart':
        await pm2.restart(worker_name);
        break;
      case 'start':
        await pm2.start(worker_name);
        break;
      case 'stop':
        await pm2.stop(worker_name);
        break;
      case 'delete':
        await pm2.delete(worker_name);
        break;
      default:
        break;
    }
    res.json({
        code: 200,
        message: "Ok"
    });
});

