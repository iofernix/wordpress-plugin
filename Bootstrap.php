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
            $io->ask("Plugin name [$projectName] : ", $projectName),
            $io->ask("Plugin namespace [Fernix] : ", "Fernix"),
            $io->ask("Author [Fernix] : ", "Fernix"),
            $io->ask("Author email [info@fernix.io] : ", "info@fernix.io"),
            $io->ask("Copyright [$projectDate IO Fernix LLC] : ", "$projectDate IO Fernix LLC"),
            $io->ask("License [MIT] : ", "MIT")
        );

        $mapping[0] = implode('_', explode(' ', ucwords($mapping[0])));
        $mapping[1] = implode('_', explode(' ', ucwords($mapping[1])));
        $mapping[6] = implode('-', explode(' ', strtolower($mapping[0])));

        self::renameProjectFiles($projectPath, $mapping);
    }

    public static function renameProjectFiles($path, $mapping)
    {
        $directory = new \DirectoryIterator($path);
        $iterator = new \IteratorIterator($directory);

        $pluginName = $mapping[0];
        $pluginNamespace = $mapping[1];
        $pluginFilename = $mapping[6];
        $pluginAuthor = $mapping[2];
        $pluginAuthorEmail = $mapping[3];
        $pluginCopyright = $mapping[4];
        $pluginLicense = $mapping[5];

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
                            '/#\{License\}/'
                        ), array(
                            $pluginName,
                            $pluginNamespace,
                            $pluginFilename,
                            strtolower($pluginNamespace),
                            $pluginAuthor,
                            $pluginAuthorEmail,
                            $pluginCopyright,
                            $pluginLicense
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
