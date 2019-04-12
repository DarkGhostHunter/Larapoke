<script>
    // start-larapoke-script
    if (typeof larapoke_script === 'undefined') {

        let larapoke_date = new Date();

        const larapoke_script = () => {
            let ajax = new XMLHttpRequest;

            ajax.onreadystatechange = () => {
                if (ajax.readyState === 4 && ajax.status === 204) {
                    larapoke_date = new Date();
                }
                larapoke_script_expired();
            };

            ajax.open('HEAD', '{{ $route }}');
            ajax.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            ajax.send();

        };

        const larapoke_script_expired = () => {
            if (navigator.onLine && new Date() - larapoke_date >= {{ $interval }} + {{ $lifetime }}) {
                window.location.reload();
            }
        };

        setInterval(() => { larapoke_script(); }, {{ $interval }} );

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) { larapoke_script_expired(); }
        }, false);

        window.addEventListener('online', larapoke_script_expired(), false);
    }
    // end-larapoke-script
</script>
