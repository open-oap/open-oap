<?php declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class State extends AbstractEntity
{

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * internal title for TYPO3 backend view (for editors)
     *
     * @var string
     */
    protected $internalTitle = '';

    /**
     * output for legends on dashboard
     *
     * @var string
     */
    protected string $legend = '';

    /** @var string  */
    protected string $mailSubject = '';

    /***
     * @var string
     */
    protected string $mailText = '';

    protected string $functions = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getInternalTitle(): string
    {
        return $this->internalTitle;
    }

    public function setInternalTitle(string $internalTitle): void
    {
        $this->internalTitle = $internalTitle;
    }

    public function getLegend(): string
    {
        return $this->legend;
    }

    public function setLegend(string $legend): void
    {
        $this->legend = $legend;
    }

    public function getMailSubject(): string
    {
        return $this->mailSubject;
    }

    public function setMailSubject(string $mailSubject): void
    {
        $this->mailSubject = $mailSubject;
    }

    public function getMailText(): string
    {
        return $this->mailText;
    }

    public function setMailText(string $mailText): void
    {
        $this->mailText = $mailText;
    }

    public function getFunctions(): array
    {
        return $this->functions !== '' ? explode(',', $this->functions) : [];
    }

    public function setFunctions(string $functions): void
    {
//        $this->functions = $functions;
         $this->functions = implode(',', $functions);
    }

}
