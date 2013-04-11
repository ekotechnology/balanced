<?php namespace Ekotechnology\Balanced\Representations;

use Ekotechnology\Balanced\Exceptions\RequiredFieldMissing;
use Ekotechnology\Balanced\Exceptions\InvalidFieldValue;

class Card {
	use RepresentationTrait;

	const VISA = "Visa";
	const AMEX = "American Express";
	const DISC = "Discover";
	const MC = "Mastercard";

	var $brand;

	/**
	 * Validate the current card information
	 * (This obviously isn't a good idea if it isn't fresh,
	 * since the raw card number wouldn't be available)
	 * @return true
	 */
	function validate() {
		$this->checkCardNum()->checkCardExpiration()->cardBrand()->checkCVV();
		return true;
	}

	/**
	 * Checks the existence of the card number, the length,
	 * and that it passes the Luhn check
	 * @return this
	 */
	private function checkCardNum() {
		if (!$this->card_number) {
			throw new RequiredFieldMissing("Card Number not specified.");
		}
		else {
			// Remove any characters that are not a number
			// (The card number should have no spaces or special characters)
			$this->card_number = preg_replace('/[^0-9]/', '', $this->card_number);

			// Check the length of the card number
			if (strlen($this->card_number) < 13 || strlen($this->card_number) > 17) {
				throw new InvalidFieldValue("Card Number is an invalid length.");
			}
		}
		return $this;
	}

	/**
	 * Checks the existence and validity of
	 * the card expiration month and year
	 * @return this
	 */
	private function checkCardExpiration() {
		if (!$this->expiration_month) {
			throw new RequiredFieldMissing("Card Expiration Month not specified.");
		}

		if (!$this->expiration_year) {
			throw new RequiredFieldMissing("Card Expiration Year not specified.");
		}

		$monthName = date("F", mktime(0, 0, 0, $this->expiration_month));
		$expiration = new \DateTime("last day of " . $monthName . ' ' . $this->expiration_year);
		$current = new \DateTime("last day of this month");

		if ($expiration < $current) {
			throw new InvalidFieldValue("Expiration " . $this->expiration_month . '/' . $this->expiration_year . ' has already passed.');
		}
	    return $this;
	}

	/**
	 * Determines the brand of the card
	 * and sets the proper property
	 * @return this
	 */
	private function cardBrand() {
		$number = $this->card_number;
		$first = $number[0];
		$ftwo = $number[0] . '' . $number[1];
		$ffour = $number[0] . '' . $number[1] . '' . $number[2] . '' . $number[3];

		if ($number[0] == 4) {
			$this->brand = Card::VISA;
		}
		elseif ($ftwo === '34' || $ftwo === '35' || $ftwo === "36" || $ftwo == "37") {
			$this->brand = Card::AMEX;
		}
		elseif ($ftwo === '52' || $ftwo === '53' || $ftwo == '54' || $ftwo == '55') {
			$this->brand = Card::MC;
		}
		elseif ($ffour == '6011') {
			$this->brand = Card::DISC;
		}
	    return $this;
	}

	/**
	 * If the CVV code is defined, it checks that
	 * it matches the requirements for the card brand
	 * (4 digits AMEX, 3 everybody else)
	 * @return $this
	 */
	private function checkCVV() {
		if ($this->security_code) {
			$this->security_code = preg_replace('/[^0-9]/', '', $this->security_code);
			if ($this->brand === Card::AMEX && strlen($this->security_code) !== 4) {
				throw new InvalidFieldValue("Security Code for American Express must be 4 digits.");
			}
			elseif ($this->brand !== Card::AMEX && strlen($this->security_code) !== 3) {
				throw new InvalidFieldValue("Security Code for non American Express cards must be 3 digits.");
			}
		}
		return $this;
	}
}