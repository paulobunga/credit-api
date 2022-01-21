const pm2 = require("pm2");

const pm2Client = {
    _instance: pm2,
    list() {
        return new Promise((resolve, reject) => {
            this._instance.list((err, list) => {
                // console.log(err, list);
                if (err) {
                    reject(err);
                } else {
                    resolve(list);
                }
            });
        });
    },
};

module.exports = pm2Client;
