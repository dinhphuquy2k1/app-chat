require('./bootstrap');
$(document).ready(function(){

    Pusher.logToConsole = true;

    window.Echo.join('user-active-channel-1')
    .here((users) => {
        console.log(users);
    })
    .joining((user) => {
        console.log(user.name);
    })
    .leaving((user) => {
        console.log(user.name);
    })
    .error((error) => {
        console.error(error);
    });
});
