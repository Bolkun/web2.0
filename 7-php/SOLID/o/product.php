 <?php
 
	protected $logger;
     
    public function __construct(ILogger $logger) {
        $this->logger = $logger;
    }
 
    public function setPrice($price) {
        try {
            // save price in db
        } catch (DbException $e) {
            $this->logger->log($e->getMessage());
        }
    }