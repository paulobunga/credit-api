
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