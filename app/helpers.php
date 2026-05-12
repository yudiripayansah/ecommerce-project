<?php

if (! function_exists('rupiah')) {
    function rupiah(int|float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
