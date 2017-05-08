<?php

class ULBuilder extends RecursiveIteratorIterator
{
    protected $html = '';

    /**
     * {@inheritdoc}
     */
    public function beginIteration()
    {
        $this->addToList('<ul>');
    }

    /**
     * {@inheritdoc}
     */
    public function endIteration()
    {
        $this->addToList('</ul>');
    }

    /**
     * {@inheritdoc}
     */
    public function beginChildren()
    {
        $this->addToList('<li><ul>');
    }

    /**
     * {@inheritdoc}
     */
    public function endChildren()
    {
        $this->addToList('</ul></li>');
    }

    /**
     * Add a string to the list.
     *
     * @param $string
     */
    public function addToList($string)
    {
        $this->html = $this->html . "\n" . $string;
    }

    public function getList()
    {
        return $this->html;
    }
}
