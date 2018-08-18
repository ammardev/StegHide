<?php

/**
 * Class StegHide
 *
 * A class used to hide text inside an image using LSB Algorithm.
 * This class is for education purpose only and not for using in an production environment.
 *
 * @link https://3alam.pro/3mmarg97/articles/steganography-using-lsb-implementation-in-php LSB lession in Arabic.
 * @author Ammar Al-khawaldeh
 * @license MIT
 */
class StegHide {

    protected $image;

    public function __construct(string $imagePath) {
        $this->image = new Imagick($imagePath);
    }

    public function setImage(string $path) {
        $this->image = new Imagick($path);
    }

    public function saveImage(string $name) {
        $this->image->writeImage($name . '.png');
    }

    public function hideText(string $text): int {
        $binaryText = $this->stringToBin($text);
        $pointer = 0;
        $shouldStop = false;

        $iterator = $this->image->getPixelIterator();
        foreach ($iterator as $row => $pixels) {
            foreach ($pixels as $column => $pixel) {
                $colors = $this->colorToBin($pixel);
                for ($i = 0; $i < 3; $i++) {
                    $colors[$i][7] = $binaryText[$pointer];
                    $shouldStop = (++$pointer == strlen($binaryText));
                    if ($shouldStop)
                        break;
                }
                $pixel->setColor($this->binToColor($colors));

                if ($shouldStop)
                    break;
            }
            $iterator->syncIterator();
            if ($shouldStop)
                break;
        }

        return strlen($binaryText);
    }

    public function showText(int $length): string {
        $iterator = $this->image->getPixelIterator();
        $binaryText = '';

        foreach ($iterator as $row => $pixels) {
            foreach ($pixels as $column => $pixel) {
                $colors = $this->colorToBin($pixel);
                foreach ($colors as $color) {
                    $binaryText .= $color[7];
                    if (strlen($binaryText) == $length)
                        return $this->binToString($binaryText);
                }
            }
        }
        return false;
    }

    protected function binToString(string $binary): string {
        $string = '';
        $start = 0;
        while ($start <= strlen($binary)) {
            $string .= chr(bindec(substr($binary, $start, 8)));
            $start += 8;
        }
        return $string;
    }

    protected function stringToBin(string $string): string {
        $binary = '';
        for ($i = 0; $i < strlen($string); $i++)
            $binary .= sprintf('%08b', ord($string[$i]));

        return $binary;
    }

    protected function colorToBin(ImagickPixel $pixel): array {
        $colors = $pixel->getColor();

        return [
            sprintf('%08b', $colors['r']),
            sprintf('%08b', $colors['g']),
            sprintf('%08b', $colors['b'])
        ];
    }

    protected function binToColor(array $colors): string {
        $r = bindec($colors[0]);
        $g = bindec($colors[1]);
        $b = bindec($colors[2]);
        return "rgb($r, $g, $b)";
    }
}