<?php

class Sydney_Server_Tools
{
    /**
     * Returns the servername and server aliases from the Apache config file
     * @param String $path Path to the dir where the config files are installed
     */
    static public function getSiteAvailableOnApache($path = '/etc/apache2/sites-available/default')
    {
        $cmd = 'cat ' . $path . '/* | grep -E "ServerName|ServerAlias"  | sed \'s/ServerName\|ServerAlias//\' | tr -s \'\n\' \' \' ';
        exec($cmd, $exs);

        $sites = array();
        foreach (preg_split('/ /', $exs[0]) as $v) {
            if (trim($v) != '') {
                $sites[$v] = array(
                    'label' => $v,
                    'url'   => 'http://' . $v,
                    'parts' => preg_split('/\./', $v)
                );
            }
        }
        sort($sites);

        return $sites;
    }

    /**
     * get the awstats paths
     */
    static public function getAWstatsNames($path = '/etc/awstats', $url = '/cgi-bin/awstats.pl?config=')
    {
        $cmd2 = 'for i in `ls ' . $path . '/awstats.*.conf`; do basename $i | sed s/awstats.// | sed s/.conf//; done';
        exec($cmd2, $exs2);
        $toret = array();
        foreach ($exs2 as $e) {
            $toret[] = array('label' => $e, 'url' => $url . $e);
        }

        return $toret;
    }

    /**
     * Returns the server's uptime
     */
    static public function getUptime()
    {
        exec('uptime', $r);

        return $r;
    }

    /**
     * Returns the OS release (Ubuntu)
     */
    static public function getOSrelease()
    {
        exec('lsb_release -a', $r);

        return $r;
    }

    /**
     * Returns the server date
     */
    static public function getCurrentDate()
    {
        exec('df -h', $r);

        return $r;
    }

    /**
     * Returns the space occupied by the webinstances
     * @param string $instancesPath
     */
    static public function getWebinstancesDiskUsed($instancesPath = '/home/jtonneau/www/sydney/webinstances/')
    {
        $df = self::getDiskFreeInfo($instancesPath);
        $toret = array();
        exec('du -sk ' . $instancesPath . '/*', $r);
        foreach ($r as $a) {
            $u = preg_split("/\\t/", $a);
            $toret[] = array(
                'bytes'    => $u[0],
                'human'    => self::bytesToHuman($u[0]),
                'path'     => $u[1],
                'basename' => basename($u[1]),
                'perconhd' => round($u[0] / $df['size'] * 100, 2)
            );
        }

        return $toret;
    }

    /**
     * Returns disk space info
     * @param String $path
     */
    static public function getDiskFreeInfo($path = '/home/jtonneau/www/sydney/')
    {
        exec('df ' . $path, $r1);
        $df1 = preg_split('/[ ]{1,10}/', $r1[0]);
        $df2 = preg_split('/[ ]{1,10}/', $r1[1]);
        $diskInfo = array();
        $i = 0;
        $blocks = 1;
        foreach ($df1 as $k) {
            if ($k == 'Avail') {
                $k = 'Available';
            }
            if (preg_match('/-blocks$/', $k)) {
                preg_split('/-/', $k);
                $blocks = $k;
            }
            $diskInfo[($k)] = $df2[$i];
            $i++;
        }
        $diskInfo['size'] = $diskInfo['Used'] + $diskInfo['Available'];
        if ($blocks != 1 && $blocks != '1K') {
            foreach ($diskInfo as $k => $v) {
                if (preg_match('/^[0-9.]{1,50}$/', $v)) {
                    $diskInfo[$k] = $v * ($blocks / 1024);
                }
            }
        }

        return $diskInfo;
    }

    /**
     * Returns a human readable format from bytes
     * @param float $b
     */
    static public function bytesToHuman($b)
    {
        $unit = array('b', 'Mo', 'Go', 'To');
        $n = $b;
        $i = 0;
        while ($n >= 1024) {
            $n = $n / 1024;
            $i++;
        }

        return round($n, 2) . ' ' . $unit[$i];
    }

}
