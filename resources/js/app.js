require('./bootstrap');
$(document).ready(function(){

    Pusher.logToConsole = true;
    // var pusher = new Pusher('5fb0beef9243b1d26f66', {
    //     cluster: 'ap1',
    //     authEndpoint: 'pusher/auth',
    //     auth: {
    //         headers: {
    //             'X-CSRF-TOKEN': '{{ csrf_token() }}',
    //             "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTcyLjE2LjIxLjE2ODo4MDAwL2F1dGgvbG9naW4iLCJpYXQiOjE2ODk2NDg2MzIsImV4cCI6MTY4OTY1OTQzMiwibmJmIjoxNjg5NjQ4NjMyLCJqdGkiOiI0UWZtTk9QM2ttcXp6YzBtIiwic3ViIjoiMSIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.h0SuG4uZNFKswewffaFPfR7AuNgUGVYn14ro9IZ5HXU",
    //             "Access-Control-Allow-Origin": "*"
    //         }
    //     }
    // });
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
//
// console.log(window.Echo);
// window.Echo.join('user-active-channel')
//    .here((users) => {
//     console.log(users);
//    }).joining((user) => {
//     console.log(user, 'joined');
//    })
