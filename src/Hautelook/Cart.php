<?php
namespace Hautelook;

class Cart
{
    private $items;
    private $price, $quantity, $weight;
    private $discount;

    public function __construct() {
        // the item list in the shopping cart
        $items = array();

        // the attribute label for each item
        $this->price = "price";
        $this->quantity = "quantity";
        $this->weight = "weight";

        $this->discount = 1.0;
    }

    public function subtotal()
    {
        $totalPrice = 0;
        foreach ($this->items as $name => $attr)
        {
            $totalPrice += $this->items[$name][$this->price] *
                           $this->items[$name][$this->quantity];
        }

        return $totalPrice * $this->discount;
    }

    public function shippingFee($flatRate = 5, $overRate = 20,
                                $priceLimit = 100, $weightLimit = 10)
    {
        $totalPrice = $this->subtotal();

        $weightInfo= $this->getWeightInfo();
        $totalWeight = $weightInfo[0];
        $overWeight = $weightInfo[1];
        $overQuantity = $weightInfo[2];

        $shippingFee = $overRate * $overQuantity;

        if ($totalPrice < $priceLimit &&
            $totalWeight > $overWeight &&
            $totalWeight < $overWeight + $weightLimit)
        {
            $shippingFee += $flatRate;
        }
        return $shippingFee;
    }

    public function total()
    {
        return $this->subtotal() + $this->shippingFee();
    }

    public function addItem($name, $price, $weight = 0)
    {
        if (!$this->isInteger($price) ||
            !$this->isInteger($weight))
        {
            return false;
        }

        $this->items[$name][$this->price] = $price;

        $this->items[$name][$this->quantity] =
            (isset($this->items[$name][$this->quantity])) ?
            ($this->items[$name][$this->quantity] + 1) : 1;

        $this->items[$name][$this->weight] = $weight;

        return true;
    }

    public function getItemQuantity($name)
    {
        if (!isset($this->items[$name][$this->quantity]))
        {
            return 0;
        }
        return $this->items[$name][$this->quantity];
    }

    private function getWeightInfo($weightLimit = 10)
    {
        $overQuantity = 0;
        $overWeight = 0;
        $totalWeight = 0;

        foreach ($this->items as $name => $attr)
        {
            $curQuantity = $this->items[$name][$this->quantity];
            $curWeight = $this->items[$name][$this->weight];
            if ($curWeight >= $weightLimit)
            {
                $overQuantity += $curQuantity;
                $overWeight += $curWeight * $curQuantity;
            }
            $totalWeight += $curWeight;
        }

        return array($totalWeight, $overWeight, $overQuantity);
    }

    public function addCoupon($coupon)
    {
        if (!$this->isInteger($coupon) ||
            $coupon > 100 || $coupon < 0)
        {
            return false;
        }

        $this->discount *= (1 - $coupon / 100.0);
        return true;
    }

    private function isInteger($int)
    {
        return preg_match('/^[0-9]+$/', $int);
    }
}
