 <?php
 
	protected $logger;
     
    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }
 
    public function setPrice($price) {
        try {
            // save price in db
        } catch (DbException $e) {
            $this->logger->log($e->getMessage());
        }
    }