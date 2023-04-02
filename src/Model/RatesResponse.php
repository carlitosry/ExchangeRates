<?php

namespace App\Model;

class RatesResponse
{
    private string $base;
    private bool $success;
    private array $rates;
    private string $date;

    /**
     * @param array $rates
     */
    public function __construct(array $rates = [])
    {
        $this->setRates($rates);
    }

    /**
     * @return mixed
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * @param array $rates
     */
    public function setRates(array $rates): void
    {
        $this->rates = $rates;
    }

    /**
     * @return mixed
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @param mixed $base
     */
    public function setBase($base): void
    {
        $this->base = $base;
    }

    /**
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param mixed $success
     */
    public function setSuccess($success): void
    {
        $this->success = $success;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return void
     */
    public function setDate(string $date): void
    {
        $this->date = $date;
    }

}