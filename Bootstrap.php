<?php

namespace Fernix\Script;

class Bootstrap
{
    public static function init(\Composer\Script\Event $event)
    {
        $io = $event->getIO();
        $projectPath = dirname(realpath(\Composer\Factory::getComposerFile()));
        $projectName = basename($projectPath);
        $projectDate = date("Y");

        $mapping = array(
            "pluginName" => $io->ask("Plugin name [$projectName] : ", $projectName),
            "pluginNamespace" => $io->ask("Plugin namespace [Fernix] : ", "Fernix"),
            "pluginAuthor" => $io->ask("Author [Fernix] : ", "Fernix"),
            "pluginAuthorEmail" => $io->ask("Author email [info@fernix.io] : ", "info@fernix.io"),
            "pluginCopyright" => $io->ask("Copyright [$projectDate IO Fernix LLC] : ", "$projectDate IO Fernix LLC"),
            "pluginLicense" => $io->ask("License [MIT] : ", "MIT"),
            "pluginSiteName" => $io->ask("Site name [$projectName] : ", $projectName)
        );

        $mapping["pluginName"] = implode('_', explode(' ', ucwords($mapping["pluginName"])));
        $mapping["pluginNamespace"] = implode('_', explode(' ', ucwords($mapping["pluginNamespace"])));
        $mapping["pluginFilename"] = implode('-', explode(' ', strtolower($mapping["pluginName"])));
        $mapping["pluginFilenamespace"] = implode('-', explode(' ', strtolower($mapping["pluginNamespace"])));

        self::renameProjectFiles($projectPath, $mapping);
    }

    public static function renameProjectFiles($path, $mapping)
    {
        $directory = new \DirectoryIterator($path);
        $iterator = new \IteratorIterator($directory);

        $pluginName = $mapping["pluginName"];
        $pluginNamespace = $mapping["pluginNamespace"];
        $pluginAuthor = $mapping["pluginAuthor"];
        $pluginAuthorEmail = $mapping["pluginAuthorEmail"];
        $pluginCopyright = $mapping["pluginCopyright"];
        $pluginLicense = $mapping["pluginLicense"];
        $pluginFilename = $mapping["pluginFilename"];
        $pluginFilenamespace = $mapping["pluginFilenamespace"];
        $pluginSiteName = $mapping["pluginSiteName"];

        foreach ($iterator as $file) {
            $filePathName = $file->getPathname();
            $fileExtension = $file->getExtension();

            if ($file->isDir() && !$file->isDot()) {
                if (preg_match('/\{plugin-name\}/', $filePathName)) {
                    $newFilePathName = preg_replace('/\{plugin-name\}/', $pluginFilename, $filePathName);

                    if (rename($filePathName, $newFilePathName)) {
                        self::renameProjectFiles($newFilePathName, $mapping);
                    }
                } else {
                    self::renameProjectFiles($filePathName, $mapping);
                }
            }

            if ($file->isFile()) {
                if (!preg_match('/\b(jpg|png|svg)\b/', $fileExtension) && preg_match('/\b(' . $pluginFilename . ')\b/', $filePathName)) {
                    $fileContent = implode("", file($filePathName));
                    $match_count = preg_match_all('/#\{Plugin_Name\}|#\{Plugin_Namespace\}|#\{plugin-name\}/', $fileContent);

                    if ($match_count) {
                        $fp = fopen($filePathName, 'w');

                        $fileContent = preg_replace(array(
                            '/#\{Plugin_Name\}/',
                            '/#\{Plugin_Namespace\}/',
                            '/#\{plugin-name\}/',
                            '/#\{plugin-namespace\}/',
                            '/#\{Author\}/',
                            '/#\{Author_Email\}/',
                            '/#\{Copyright\}/',
                            '/#\{License\}/',
                            '/#\{site-name\}/'
                        ), array(
                            $pluginName,
                            $pluginNamespace,
                            $pluginFilename,
                            $pluginFilenamespace,
                            $pluginAuthor,
                            $pluginAuthorEmail,
                            $pluginCopyright,
                            $pluginLicense,
                            $pluginSiteName
                        ), $fileContent);

                        fwrite($fp, $fileContent, strlen($fileContent));
                        fclose($fp);
                    }
                }

                if (preg_match('/\{plugin-name\}/', $filePathName)) {
                    $newFilePathName = preg_replace('/\{plugin-name\}/', $pluginFilename, $filePathName);

                    echo $newFilePathName;

                    if (rename($filePathName, $newFilePathName)) {
                        echo "\033[0;32m SUCCESS\033[0m\n";
                    } else {
                        echo "\033[0;31m FAIL\033[0m\n";
                    }
                }
            }
        }
    }
}
