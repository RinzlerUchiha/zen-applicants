<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BasicMath extends Model
{
    protected $table = 'tblapp_basicmath';
    protected $primaryKey = 'math_id';
    public $timestamps = false;

    protected $guarded = ['math_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'app_id', 'app_id');
    }

    public static function showAnswerList()
    {
        $list = [
            1 => [
                'question' => '1. A customer is buying a ring and a pair of earrings, the ring’s price is P 4,598.00 while the pair of earring’s price is P 12,355.00. What is the total amount the customer has to pay for both items?',
                'answer' => [
                    'a' => 'A. 16, 953.00',
                    'b' => 'B. 16, 843.00',
                    'c' => 'C. 16, 935.00',
                    'd' => 'D. 16, 853.00',
                ]
            ],
            2 => [
                'question' => '2. Trisha is saving to buy a ring that costs P 7,586.00, so far she has saved P 2,150.00. How much she still needs to save for her to be able to buy the ring?',
                'answer' => [
                    'a' => 'A. 5, 436.00',
                    'b' => 'B. 4, 536.00',
                    'c' => 'C. 9, 736.00',
                    'd' => 'D. 9, 836.00',
                ]
            ],
            3 => [
                'question' => '3. If Jenny is buying a pendant that is on 10% SALE, and the price of the pendant is P 3,450.00. What is the amount of the 10% SALE discount Jenny availed?',
                'answer' => [
                    'a' => 'A. 345.00',
                    'b' => 'B. 450.00',
                    'c' => 'C. 1, 345.00',
                    'd' => 'D. 1, 450.00',
                ]
            ],
            4 => [
                'question' => '4. Gia is buying a necklace on 3 months installment, the price of the necklace is P 14,680.00. How much will Gia on a monthly basis for the next 3 months?',
                'answer' => [
                    'a' => 'A. 9, 787.33',
                    'b' => 'B. 9, 877.33',
                    'c' => 'C. 4, 893.33',
                    'd' => 'D. 4, 983.33',
                ]
            ],
            5 => [
                'question' => '5. If Carol is buying a bracelet on SALE at P 22,400.00 and the original price is P 28,000.00. What is the percentage discount given to Carol? ',
                'answer' => [
                    'a' => 'A. 5%',
                    'b' => 'B. 10%',
                    'c' => 'C. 15%',
                    'd' => 'D. 20%',
                ]
            ],
            6 => [
                'question' => '6. If the anklet Gina wants to buy is on SALE at 15%, and the original price is P 6,800.00. How much is her discount?',
                'answer' => [
                    'a' => 'A 1, 010.00',
                    'b' => 'B 1, 020.00',
                    'c' => 'C 1, 030.00',
                    'd' => 'D 1, 0140.00',
                ]
            ],
            7 => [
                'question' => '7. Customer A is buying a ring at P 3,956.00 and customer B is buying a pair of earrings at P 6,796.00. How much is the combine total amount customer A and B paid?',
                'answer' => [
                    'a' => 'A. 10, 572.00',
                    'b' => 'B. 10, 272.00',
                    'c' => 'C. 10, 352.00',
                    'd' => 'D. 10, 752.00',
                ]
            ],
            8 => [
                'question' => '8. Lanie is buying a bracelet, the price of the bracelet is P 11,590.00. If Lanie paid P 2,500.00 cash as down payment, what is the balance amount that Lanie has to pay?',
                'answer' => [
                    'a' => 'A. 8, 920.00',
                    'b' => 'B. 9, 820.00',
                    'c' => 'C. 9, 090.00',
                    'd' => 'D. 9, 280.00',
                ]
            ],
            9 => [
                'question' => '9. Maria is buying a set of jewelry on installment basis at the price of P 158,765.00. If she made a 20% cash deposit. How much cash did she pay?',
                'answer' => [
                    'a' => 'A. 127, 012.00',
                    'b' => 'B. 127, 120.00',
                    'c' => 'C. 31, 753.00',
                    'd' => 'D. 31, 573.00',
                ]
            ],
            10 => [
                'question' => '10. Ana is buying a necklace on installment basis, the price of the necklace is P 25,764.00. How much will her monthly payment be for a 5 months installment plan?',
                'answer' => [
                    'a' => 'A. 5, 512.00',
                    'b' => 'B. 5, 152.80',
                    'c' => 'C. 5, 215.00',
                    'd' => 'D. 5, 515.00',
                ]
            ],
            11 => [
                'question' => '11. Rose is buying a ring on SALE at P 8,330.00 and the original price is P 9,800.00. What is the percentage discount given to Rose?',
                'answer' => [
                    'a' => 'A. 10%',
                    'b' => 'B. 15%',
                    'c' => 'C. 20%',
                    'd' => 'D. 25%',
                ]
            ],
            12 => [
                'question' => '12. If the bracelet Lorna wants to buy is on 5% SALE, and the original price is P 12,900.00. How much is her discount?',
                'answer' => [
                    'a' => 'A. 645.00',
                    'b' => 'B. 735.00',
                    'c' => 'C. 855.00',
                    'd' => 'D. 945.00',
                ]
            ],
        ];

        return $list;
    }
}