<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <!-- Styles -->
    <script>
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('5fb0beef9243b1d26f66', {
            cluster: 'ap1',
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTcyLjE2LjIxLjE2ODo4MDAwL2F1dGgvbG9naW4iLCJpYXQiOjE2ODk2NjIwMDYsImV4cCI6MTY4OTY3MjgwNiwibmJmIjoxNjg5NjYyMDA2LCJqdGkiOiJ6bWowN21vMEJ2MlR1WHp1Iiwic3ViIjoiMSIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.ac1Cogdj1DFh_f3z5dIi3DT7vEqdY45xXITgcYea1qo",
                }
            }
        });
        
        const channel = pusher.subscribe('presence-user-active-channel-1');
        channel.bind('pusher:subscription_succeeded', (members) => {
            console.log('Users currently in the channel:', members);
        });

        channel.bind('pusher:member_added', (member) => {
            console.log('User joined:', member.info.name);
        });

        channel.bind('pusher:member_removed', (member) => {
            console.log('User left:', member.info.name);
        });

        channel.bind('pusher:subscription_error', (error) => {
            console.error('Subscription error:', error);
        });
        // pusher.connection.bind('connected', function() {

        //     const socketId = pusher.connection.socket_id;

        //     console.log('Socket ID:', socketId);
        //     $.ajax({
        //     url: 'http://172.16.21.168:8000/pusher/auth',
        //     method: 'POST',
        //     beforeSend: function(xhr) {
        //         xhr.setRequestHeader('Authorization',
        //             'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTcyLjE2LjIxLjE2ODo4MDAwL2F1dGgvbG9naW4iLCJpYXQiOjE2ODk2NDg2MzIsImV4cCI6MTY4OTY1OTQzMiwibmJmIjoxNjg5NjQ4NjMyLCJqdGkiOiI0UWZtTk9QM2ttcXp6YzBtIiwic3ViIjoiMSIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.h0SuG4uZNFKswewffaFPfR7AuNgUGVYn14ro9IZ5HXU'
        //         );
        //     },
        //     data: {
        //         socketId: socketId,
        //         key2: 2,
        //         // Add more key-value pairs as needed
        //     },
        //     success: function(response) {
        //         // Handle the success response
        //         console.log(response);
        //     },
        //     error: function(xhr, status, error) {
        //         // Handle the error response
        //         console.error(error);
        //     }
        // });
        // });


        // var channel = pusher.subscribe('user-active-channel');
        // channel.bind('user-online-event', function(data) {
        //     console.log(data);
        // })
        // channel.bind('pusher:subscription_succeeded', (members) => {

        //     console.log(pusher);

        // });

        // channel.bind('pusher:member_added', (member) => {
        //     console.log('member_added');
        // });
        // channel.bind('pusher:subscription_succeeded', function(data) {
        //     $.ajax({
        //         url: 'http://172.16.21.168:8000/user/online',
        //         type: 'PUT',
        //         beforeSend: function(xhr) {
        //             xhr.setRequestHeader('Authorization',
        //                 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTcyLjE2LjIxLjE2ODo4MDAwL2F1dGgvbG9naW4iLCJpYXQiOjE2ODk2NDQ3NjIsImV4cCI6MTY4OTY1NTU2MiwibmJmIjoxNjg5NjQ0NzYyLCJqdGkiOiJGSndMVkZIMFIwaklQSGl4Iiwic3ViIjoiMSIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.V8EncdN9JLz3kGoVsv4pfse8ln1c4PRL7JMjly2l5rU'
        //             );
        //         },
        //         success: function(res) {
        //             console.log(pusher);
        //         },
        //         error: function(xhr, status, error) {
        //             // Xử lý phản hồi lỗi
        //         }
        //     });
        // });

        // channel.bind('pusher:member_added', (member) => {
        //     console.log('Có thành viên tham gia:', member);
        // });
        // channel.bind('pusher:member_removed', (member) => {
        //     console.log('Có thành viên rời đi:', member);
        // });

        // channel.bind('_presence-channel:user-online-event', function(data) {
        //     console.log(data);
        // });
    </script>
</head>

<body>

</body>

</html>
