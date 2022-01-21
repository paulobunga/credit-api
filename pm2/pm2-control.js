const pm2 = require("pm2");

const pm2Client = {
    _instance: pm2,
    list() {
        return new Promise((resolve, reject) => {
            this._instance.list((err, list) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(list);
                }
            });
        });
    },
    restart(worker_name) {
        return new Promise((resolve, reject) => {
            this._instance.restart(worker_name, (err, proc) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(proc);
                }
            });
        });
    },
    start(worker_name) {
      return new Promise((resolve, reject) => {
            this._instance.start(worker_name, (err, apps) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(apps);
                }
            });
        });
    },
    stop(worker_name) {
      return new Promise((resolve, reject) => {
            this._instance.stop(worker_name, (err, apps) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(apps);
                }
            });
        });
    },
    delete(worker_name) {
        return new Promise((resolve, reject) => {
            this._instance.delete(worker_name, (err, apps) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(apps);
                }
            });
        });
    },
};

module.exports = pm2Client;
