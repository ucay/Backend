<?php

namespace Persona\Hris\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Persona\Hris\Core\Logger\ActionLoggerAwareInterface;
use Persona\Hris\Core\Logger\ActionLoggerAwareTrait;
use Persona\Hris\Employee\Model\EmployeeInterface;
use Persona\Hris\Salary\Model\BenefitInterface;
use Persona\Hris\Salary\Model\EmployeeBenefitInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="sa_salary_benefits")
 *
 * @ApiResource(
 *     attributes={
 *         "filters"={"order.filter"},
 *         "normalization_context"={"groups"={"read"}},
 *         "denormalization_context"={"groups"={"write"}}
 *     }
 * )
 *
 * @author Muhamad Surya Iksanudin <surya.iksanudin@personahris.com>
 */
class SalaryBenefit implements EmployeeBenefitInterface, ActionLoggerAwareInterface
{
    use ActionLoggerAwareTrait;
    use Timestampable;
    use SoftDeletable;

    /**
     * @Groups({"read"})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     *
     * @var string
     */
    private $id;

    /**
     * @Groups({"read", "write"})
     * @ORM\ManyToOne(targetEntity="Persona\Hris\Entity\Employee", fetch="EAGER")
     * @ORM\JoinColumn(name="employee_id", referencedColumnName="id")
     * @Assert\NotBlank()
     *
     * @var EmployeeInterface
     */
    private $employee;

    /**
     * @Groups({"read", "write"})
     * @ORM\ManyToOne(targetEntity="Persona\Hris\Entity\Benefit", fetch="EAGER")
     * @ORM\JoinColumn(name="benefit_id", referencedColumnName="id")
     * @Assert\NotBlank()
     *
     * @var BenefitInterface
     */
    private $benefit;

    /**
     * @Groups({"read", "write"})
     * @ORM\Column(type="float", scale=27, precision=2)
     * @Assert\NotBlank()
     *
     * @var float
     */
    private $percentageFromBasicSalary;

    /**
     * @Groups({"read", "write"})
     * @ORM\Column(type="float", scale=27, precision=2)
     * @Assert\NotBlank()
     *
     * @var float
     */
    private $percentageFromBenefitValue;

    /**
     * @Groups({"read", "write"})
     * @ORM\Column(type="float", scale=27, precision=2)
     * @Assert\NotBlank()
     *
     * @var float
     */
    private $benefitValue;

    /**
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $usePercentage;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return EmployeeInterface
     */
    public function getEmployee(): EmployeeInterface
    {
        return $this->employee;
    }

    /**
     * @param EmployeeInterface $employee
     */
    public function setEmployee(EmployeeInterface $employee = null): void
    {
        $this->employee = $employee;
    }

    /**
     * @return BenefitInterface
     */
    public function getBenefit(): BenefitInterface
    {
        return $this->benefit;
    }

    /**
     * @param BenefitInterface $benefit
     */
    public function setBenefit(BenefitInterface $benefit = null): void
    {
        $this->benefit = $benefit;
    }

    /**
     * @return float
     */
    public function getPercentageFromBasicSalary(): ? float
    {
        return $this->percentageFromBasicSalary;
    }

    /**
     * @param float $percentageFromBasicSalary
     */
    public function setPercentageFromBasicSalary(float $percentageFromBasicSalary)
    {
        $this->percentageFromBasicSalary = $percentageFromBasicSalary;
    }

    /**
     * @return float
     */
    public function getPercentageFromBenefitValue(): ? float
    {
        return $this->percentageFromBenefitValue;
    }

    /**
     * @param float $percentageFromBenefitValue
     */
    public function setPercentageFromBenefitValue(float $percentageFromBenefitValue)
    {
        $this->percentageFromBenefitValue = $percentageFromBenefitValue;
    }

    /**
     * @return float
     */
    public function getBenefitValue(): ? float
    {
        return (float) $this->benefitValue;
    }

    /**
     * @param float $benefitValue
     */
    public function setBenefitValue(float $benefitValue)
    {
        $this->benefitValue = $benefitValue;
    }

    /**
     * @return bool
     */
    public function isUsePercentage(): bool
    {
        return $this->usePercentage;
    }

    /**
     * @param bool $usePercentage
     */
    public function setUsePercentage(bool $usePercentage)
    {
        $this->usePercentage = $usePercentage;
    }
}
