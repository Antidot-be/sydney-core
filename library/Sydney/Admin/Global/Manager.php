<?php

/**
 * Sets of tools for managing the webinstance creation and admin.
 *
 * @author arnaud
 * @since 16/10/10
 */
class Sydney_Admin_Global_Manager
{
    /**
     * Row on which we'll base the data for creation of the webinstance
     * @var Zend_Db_Table_Row
     */
    protected $row = false;
    protected $log = array();
    protected $sDB = false;
    protected $rootPath = '/www/sydney';
    protected $rootInstancesPath = '/webinstances';
    protected $zippedInstancePath = '/core/util/';
    protected $zipfileName = 'com.antidot.template.zip';
    protected $apacheVhostsPath = '/etc/apache2/sites-available/';

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->sDB = new Safinstances();
    }

    /**
     * Sets the ID of the instance and gets the row from the db
     * @param int $id
     * @return Boolean
     */
    public function setInstanceId($id)
    {
        $rows = $this->sDB->find($id);
        if (count($rows) == 1) {
            $this->row = $rows[0];

            return true;
        } else {
            $this->log[] = 'No row found, or too many';

            return false;
        }
    }

    /**
     * Sets the row which is the reference for the data we will create
     * @param Zend_Db_Table_Row $row
     * @return Boolean
     */
    public function setRow(Zend_Db_Table_Row $row)
    {
        $this->row = $row;

        return true;
    }

    /**
     * Creates the webinstance structure (files, folders and so on)
     * @return Boolean
     */
    public function createWebInstanceStructure()
    {
        if ($this->row) {
            $tmplPath = $this->rootPath . $this->rootInstancesPath . '/com.antidot.template';
            $instPath = $this->rootPath . $this->rootInstancesPath . '/' . $this->row->rootpath;
            if (file_exists($tmplPath)) {
                unlink($tmplPath);
            }
            if (file_exists($instPath)) {
                $this->log[] = 'Instance files already exist.';

                return false;
            } else {
                // unpacking the zip in the right dir
                $zip = new ZipArchive();
                $res = $zip->open($this->rootPath . $this->zippedInstancePath . '/' . $this->zipfileName);
                if ($res === true) {
                    $this->log[] = 'File extracted';
                    $zip->extractTo($this->rootPath . $this->rootInstancesPath . '/');
                    $zip->close();
                    // renaming instance
                    if (rename($tmplPath, $instPath)) {
                        $this->log[] = 'Rename successfull.';

                        return $this->buildConfigFile();
                    } else {
                        $this->log[] = 'Could not rename the file.';

                        return false;
                    }
                } else {
                    $this->log[] = 'Failed to unzip, code:' . $res;

                    return false;
                }
            }
        } else {
            $this->log[] = 'The row is empty';

            return false;
        }
    }

    /**
     *
     * @return void
     */
    public function buildConfigFile()
    {
        $configFilePath = $this->rootPath . $this->rootInstancesPath . '/' . $this->row->rootpath . '/config/config.default.ini';
        $cfg = '[general]
general.siteTitle="' . $this->row->label . '"
general.siteEmail="info@' . $this->row->domain . '"
general.baseUrl=
general.defaultmodule=publicms
general.lang=en
general.locale=en_US
general.layout=layoutMain
general.env=PROD
;; Web instance specific
general.webinstance=' . $this->row->rootpath . '
db.safinstances_id=' . $this->row->id . '
admin.onlineedition="yes"
;general.skipblankpages=yes
general.useSEOUrl=true

[development : general]

[developerAS : development]

[developerFA : development]

[developerGD : development]
';
        if ($handle = fopen($configFilePath, 'w+')) {
            if (fwrite($handle, $cfg)) {
                fclose($handle);
                $this->log[] = 'Config file updated.';

                return true;
            } else {
                $this->log[] = 'Could not write the config file.';

                return false;
            }
        } else {
            $this->log[] = 'Could not open the config file.';

            return false;
        }

    }

    public function createSitemapCronjobFile()
    {
        $sitemapFilePath = $this->rootPath . $this->rootInstancesPath . '/' . $this->row->rootpath . '/application/cronjob/sitemap.php';
        $sitemapContent = '#!/usr/bin/php
<?php

include_once(\'../../../../core/CLI/Sitemap.php\');

$webinstance = ' . $this->row->id . ';

$sitemap = new Sitemap($webinstance);
$sitemap->run();
';
        if ($handle = fopen($sitemapFilePath, 'w+')) {
            if (fwrite($handle, $sitemapContent)) {
                fclose($handle);
                $this->log[] = 'Sitemap (cronjob) file updated.';

                return true;
            } else {
                $this->log[] = 'Could not write the Sitemap (cronjob) file.';

                return false;
            }
        } else {
            $this->log[] = 'Could not open the Sitemap (cronjob) file.';

            return false;
        }
    }

    /**
     * Creates the apache config file
     * @return Boolean
     */
    public function createApacheConfigFile()
    {
        if ($this->row) {
            $cfgFileName = $this->apacheVhostsPath . $this->row->rootpath . ".conf";
            if (file_exists($apacheVhostsPath)) {
                $this->log[] = 'Apache file already exists.';

                return false;
            } else {
                $acfg = '<VirtualHost *:80>
  DocumentRoot /www/sydney/webinstances/' . $this->row->rootpath . '/html
  ServerName ' . $this->row->domain;
                if ($this->row->secdomains != '') {
                    $acfg .= '
  ServerAlias ' . $this->row->secdomains;
                }
                $acfg .= '
	CustomLog /var/log/apache2/' . $this->row->rootpath . '.log combined
        <Directory "/www/sydney/webinstances/' . $this->row->rootpath . '/html/">
               Options Indexes FollowSymLinks
               AllowOverride All
               Order allow,deny
               Allow from all
        </Directory>
</VirtualHost>
';

                if ($handle = fopen($cfgFileName, 'w+')) {
                    if (fwrite($handle, $acfg)) {
                        fclose($handle);
                        $this->log[] = 'Apache config file updated.';

                        return true;
                    } else {
                        $this->log[] = 'Could not write the Apache config file.';

                        return false;
                    }
                } else {
                    $this->log[] = 'Could not open the Apache config file.';

                    return false;
                }

                $this->log[] = 'Apache file created.';

                return true;
            }
        } else {
            $this->log[] = 'The row is empty';

            return false;
        }
    }

    /**
     * Returns the log in a flat format
     * @return string
     */
    public function getFlatLog()
    {
        return implode("\n", $this->log);
    }

    public function getLog()
    {
        return $this->log;
    }

}
