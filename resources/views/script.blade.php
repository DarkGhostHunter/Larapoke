
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
            };

            ajax.open('HEAD', '{{ $route }}');
            ajax.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            ajax.send();
        };

        setInterval(() => { larapoke_script(); }, {{ $interval }} * 1000);

        @if($timeout)@include('larapoke::timeout')@endif
    }
    // end-larapoke-script
</script>
