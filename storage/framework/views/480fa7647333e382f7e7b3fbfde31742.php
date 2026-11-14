<?php if($p->level === 'out'): ?>
    <span class="inline-block rounded-full bg-red-100 text-red-700 px-2.5 py-0.5 text-xs font-medium whitespace-nowrap">Out of stock</span>
<?php elseif($p->level === 'low'): ?>
    <span class="inline-block rounded-full bg-amber-100 text-amber-800 px-2.5 py-0.5 text-xs font-medium whitespace-nowrap">Running low</span>
<?php else: ?>
    <span class="inline-block rounded-full bg-green-100 text-green-800 px-2.5 py-0.5 text-xs font-medium whitespace-nowrap">In stock</span>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/products/_level.blade.php ENDPATH**/ ?>