<?php

namespace Chriswest101\Paynow\Tests\Unit;

use Chriswest101\Paynow\Facades\Paynow;
use Chriswest101\Paynow\Tests\TestCase;
use DateTime;
use InvalidArgumentException;

class PaynowTest extends TestCase
{
    /** @test */
    public function the_pay_now_service_will_fail_gracefully_if_no_uen_or_mobile_is_provided()
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        Paynow::generate(
            100.00,
            false,
            "O123456",
            (new DateTime())->modify("+ 1 hour"),
            "Clothing Company Pte Ltd",
            "SG",
            "Singapore",
            null,
            null,
            false
        );
    }

    /** @test */
    public function the_pay_now_service_will_fail_gracefully_if_an_amount_of_zero_is_provided()
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        Paynow::generate(
            0,
            false,
            "O123456",
            (new DateTime())->modify("+ 1 hour"),
            "Clothing Company Pte Ltd",
            "SG",
            "Singapore",
            null,
            null,
            false
        );
    }
    
    /** @test */
    public function the_pay_now_service_will_fail_gracefully_if_an_amount_less_than_zero_is_provided()
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        Paynow::generate(
            -1,
            false,
            "O123456",
            (new DateTime())->modify("+ 1 hour"),
            "Clothing Company Pte Ltd",
            "SG",
            "Singapore",
            null,
            null,
            false
        );
    }
    
    /** @test */
    public function the_pay_now_service_will_fail_gracefully_if_a_blank_company_name_is_provided()
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        Paynow::generate(
            100.00,
            false,
            "O123456",
            (new DateTime())->modify("+ 1 hour"),
            "",
            "SG",
            "Singapore",
            null,
            null,
            false
        );
    }
    
    /** @test */
    public function the_pay_now_service_will_fail_gracefully_if_a_blank_merchant_country_is_provided()
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        Paynow::generate(
            100.00,
            false,
            "O123456",
            (new DateTime())->modify("+ 1 hour"),
            "",
            "",
            "Singapore",
            null,
            null,
            false
        );
    }
    
    /** @test */
    public function the_pay_now_service_will_fail_gracefully_if_a_blank_merchant_city_is_provided()
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        Paynow::generate(
            100.00,
            false,
            "O123456",
            (new DateTime())->modify("+ 1 hour"),
            "",
            "SG",
            "",
            null,
            null,
            false
        );
    }
    
    /** @test */
    public function the_pay_now_service_will_fail_gracefully_if_an_expiry_date_in_the_past_is_provided()
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        Paynow::generate(
            100.00,
            false,
            "O123456",
            (new DateTime())->modify("- 1 hour"),
            "",
            "SG",
            "",
            null,
            null,
            false
        );
    }

    /** @test */
    public function the_pay_now_service_can_generate_a_paynow_compatible_qr_code_string_using_a_uen_number()
    {
        // Arrange
        $expected = "00020101021226500009SG.PAYNOW0101202112020111104G030100408202011125204000053037025406100.005802SG5924Clothing Company Pte Ltd6009Singapore62110107O1234566304B015";

        // Act
        $result = Paynow::generate(
            100.00,
            false,
            "O123456",
            (new DateTime())->modify("+ 1 hour"),
            "Clothing Company Pte Ltd",
            "SG",
            "Singapore",
            "2020111104G",
            null,
            false
        );
        
        // Assert
        $this->assertSame($expected, $result);
    }

    /** @test */
    public function the_pay_now_service_can_generate_a_paynow_compatible_qr_code_string_using_a_mobile_number()
    {
        // Arrange
        $expected = "00020101021226470009SG.PAYNOW01010020882049939030100408202011125204000053037025406100.005802SG5924Clothing Company Pte Ltd6009Singapore62110107O123456630417C6";

        // Act
        $result = Paynow::generate(
            100.00,
            false,
            "O123456",
            (new DateTime())->modify("+ 1 hour"),
            "Clothing Company Pte Ltd",
            "SG",
            "Singapore",
            null,
            "82049939",
            false
        );
        
        // Assert
        $this->assertSame($expected, $result);
    }
}
