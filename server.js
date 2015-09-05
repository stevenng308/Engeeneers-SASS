/* server.js */

var seneca = require('seneca')();

seneca.use(require('./data-loader.js')).act('role:DataLoader, cmd:loadData, name:"hello world"', console.log)
