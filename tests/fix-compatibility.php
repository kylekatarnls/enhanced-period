<?php

$config = [
    '8.0' => [
        __DIR__.'/../vendor/nesbot/carbon/src/Carbon/CarbonInterface.php' => [
            'public function modify($modify);'              => '// public function modify($modify);',
            'public function setDate($year, $month, $day);' => '// public function setDate($year, $month, $day);',
            'public function setISODate($year,'             => '// public function setISODate($year,',
            'public function setTime('                      => '// public function setTime(',
            'public function setTimestamp('                 => '// public function setTimestamp(',
            'public function diff('                         => '// public function diff(',
            'public function createFromFormat('             => '// public function createFromFormat(',
        ],
    ],
];

foreach ($config as $minimumVersion => $replacements) {
    if (version_compare(PHP_VERSION, $minimumVersion, '<')) {
        continue;
    }

    foreach ($replacements as $file => $replacement) {
        $contents = file_exists($file) ? file_get_contents($file) : null;

        if (!empty($contents)) {
            $newContents = strtr($contents, $replacement);

            if ($contents !== $newContents) {
                file_put_contents($file, $newContents);
            }
        }
    }
}
