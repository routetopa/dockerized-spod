var config,
server = require('./lib/master');
const cluster = require('cluster');
const numCPUs = require('os').cpus().length;

if(process.env.CONFIG_PATH){
  config = require(process.env.CONFIG_PATH);
} else {
  config = require('./config');
}

process.env.NODE_ENV = 'production';

server.createMasterServer(config);

/*if (cluster.isMaster) {
  console.log("Master " + process.pid + " is running");

  // Fork workers.
  for (var i = 0; i < numCPUs; i++) {
     cluster.fork().on('listening', function(address){
        console.log(address);
     });
  }

  cluster.on('exit', function(worker, code, signal) {
      console.log("worker " + worker.process.pid +" died");
  });

} else {
  console.log("Worker "  + process.pid + " started");
  var s = server.createServer(config);
}*/




