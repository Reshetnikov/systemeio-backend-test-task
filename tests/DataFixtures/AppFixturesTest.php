<?php 

use App\DataFixtures\AppFixtures;
use App\Entity\Coupon;
use App\Entity\Product;
use App\Entity\TaxFormat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AppFixturesTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;


    private function clearDatabase(): void
    {
        $this->entityManager->getConnection()->beginTransaction();

        try {
            $this->entityManager->getRepository(Product::class)->createQueryBuilder('p')->delete()->getQuery()->execute();
            $this->entityManager->getRepository(Coupon::class)->createQueryBuilder('c')->delete()->getQuery()->execute();
            $this->entityManager->getRepository(TaxFormat::class)->createQueryBuilder('t')->delete()->getQuery()->execute();


            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            throw $e;
        }

        $this->entityManager->getConnection()->commit();
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $this->clearDatabase();

        $fixture = new AppFixtures();
        $fixture->load($this->entityManager);
        $this->entityManager->flush(); 
    }

    public function testProductsAreLoaded(): void
    {
        $productRepository = $this->entityManager->getRepository(Product::class);
        
        $products = $productRepository->findAll();
        $this->assertCount(3, $products);

        $this->assertEquals('Iphone', $products[0]->getName());
        $this->assertEquals(100.00, $products[0]->getPrice());
    }

    public function testCouponsAreLoaded(): void
    {
        $couponRepository = $this->entityManager->getRepository(Coupon::class);

        $coupons = $couponRepository->findAll();
        $this->assertCount(3, $coupons);


        $this->assertEquals('P10', $coupons[0]->getCode());
        $this->assertEquals('percent', $coupons[0]->getType());
        $this->assertEquals(10, $coupons[0]->getValue());
    }

    public function testTaxFormatsAreLoaded(): void
    {
        $taxFormatRepository = $this->entityManager->getRepository(TaxFormat::class);

        $taxFormats = $taxFormatRepository->findAll();
        $this->assertCount(4, $taxFormats);

        $this->assertEquals('DE', $taxFormats[0]->getCountryCode());
        $this->assertEquals('^DE\\d{9}$', $taxFormats[0]->getRegexPattern());
        $this->assertEquals(19.00, $taxFormats[0]->getTaxRate());
    }
}