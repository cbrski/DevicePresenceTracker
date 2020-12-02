@php
    $sec_diff = time() - $device['lastUsedLink']['timestamp'];

    $r = (int) ($sec_diff/(86400*7));
    $sec_diff-=$r*(86400*7);
    if (!$r)
    {
        $r = (int) ($sec_diff/86400);
        $sec_diff-=$r*86400;
        if (!$r)
        {
            $r = (int) ($sec_diff/3600);
            $sec_diff-=$r*3600;
            if (!$r)
            {
                $r = (int) ($sec_diff/60);
                $sec_diff-=$r*60;
                if (!$r)
                {
                    echo 'now';
                }
                else
                {
                    echo $r.'m <small>ago</small>';
                }
            }
            else
            {
                echo $r.'h <small>ago</small>';
            }
        }
        else
        {
            echo $r.'d <small>ago</small>';
        }
    }
    else
    {
        echo $r.'w <small>ago</small>';
    }
@endphp
