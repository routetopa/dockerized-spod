var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http, {path: "/realtime_notification"/*, transports: [ 'polling' ]*/});
var config = require('./config');

var public_room_online_users = [];

io.on('connection', function(socket){

    var user;

    socket.on('online_notification', function(msg){
        switch(msg.plugin)
        {
            case 'cocreation' :
                break;
            case 'agora' :
                console.log(public_room_online_users);

                if(!public_room_online_users["room_" + msg.room_id]) {
                    console.log("new room " + msg.room_id);
                    public_room_online_users["room_" + msg.room_id] = [];
                }

                if(public_room_online_users["room_" + msg.room_id].indexOf(msg.user_id) == -1) {
                    console.log("new user : "+msg.user_id+", for room " + msg.room_id);
                    public_room_online_users["room_" + msg.room_id].push(msg.user_id);
                    user = msg.user_id;
                }

                io.emit('online_notification_' + msg.room_id, public_room_online_users["room_" + msg.room_id]);
                break;
            default:
                break;
        }
    });

    socket.on('disconnect', function () {
        io.emit('offline_notification', user);

        for(var room in public_room_online_users)
        {
            var index = public_room_online_users[room].indexOf(user);
            if(index > -1) public_room_online_users[room].splice(index, 1);
        }
    });

    socket.on('realtime_notification', function(msg){

        switch(msg.plugin)
        {
            case 'cocreation' :
                io.emit('realtime_message_' + msg.entity_type + "_" + msg.entity_id, msg);
                break;
            case 'cocreation_discussion' :
                console.log(msg);
                io.emit('realtime_cocreation_message_' + msg.entityId, msg);
                break;
            case 'agora' :
                console.log(msg);
                io.emit('realtime_message_' + msg.room_id, msg);
                break;
            default:
                io.emit('realtime_message', msg);
                break;
        }
    });


});


http.listen(config.port, function(){
    console.log('listening on *:' + config.port);
});