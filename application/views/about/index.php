<section>
        <div class="row">
                <div class="col-md-2 col-sm-2">
                    
                </div>
                <div class="col-md-8 col-sm-8 about_us">
                    <?php foreach($items as $i): ?>
                    <h3><?php echo $i->title;?></h3>
                    <p><?php echo $i->description?></p>
                    <?php endforeach; ?>
                    
                </div>
                <div class="col-md-2 col-sm-2">
                    
                </div>
            </div> 
</section>