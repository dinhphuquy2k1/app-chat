<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Index</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    {{-- <script src="{{ asset('js/app.js') }}"></script> --}}
    <script>
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('5fb0beef9243b1d26f66', {
            cluster: 'ap1',
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTcyLjE2LjIxLjE2ODo4MDAwL2F1dGgvbG9naW4iLCJpYXQiOjE2ODk3MzM4MjIsImV4cCI6MTY4OTc0NDYyMiwibmJmIjoxNjg5NzMzODIyLCJqdGkiOiI2UHpGSWpCNDRFNHUzQ2xXIiwic3ViIjoiMSIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.eKJgX4BLU8E0-pmVIsVGgbSU61KPrhsWJ6w4ctg9MWY",
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

    </script>
</head>

<body>

</body>

</html>
