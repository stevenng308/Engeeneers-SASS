module.exports = function DataLoader(options) {
  var seneca = this;

  seneca.add('role:DataLoader, cmd:loadData', function loadData(msg, respond){
  	respond(null, {answer: msg.name})
  })

}
