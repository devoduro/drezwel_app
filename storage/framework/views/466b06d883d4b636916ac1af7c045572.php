
<?php $__env->startSection('title', 'Sign in · Drezwel Go'); ?>
<?php $__env->startSection('content'); ?>
<div class="min-h-[80vh] flex items-center">
    <div class="w-full bg-white rounded-2xl shadow p-6">
        <div class="mb-5 text-center">
            <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Drezwel Go" class="mx-auto h-14 w-auto">
            <h1 class="mt-3 text-xl font-bold">Drezwel Go</h1>
            <p class="text-sm text-slate-500">Manager &amp; rider sign in</p>
        </div>
        <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="text-sm font-medium">Phone or email</label>
                <input name="login" value="<?php echo e(old('login')); ?>" required autofocus class="mt-1 w-full rounded-lg border-slate-300 border px-3 py-2.5">
            </div>
            <div>
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="password" required class="mt-1 w-full rounded-lg border-slate-300 border px-3 py-2.5">
            </div>
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                <a class="text-brand-accent font-medium" href="<?php echo e(route('password.forgot')); ?>">Forgot password?</a>
            </div>
            <button class="w-full rounded-lg bg-[#1c3041] text-white py-2.5 font-semibold shadow hover:shadow-md transition">Sign in</button>
        </form>
        <p class="mt-5 text-center text-sm text-slate-500">Waiting for an order? <a class="text-brand-accent font-medium" href="<?php echo e(route('track')); ?>">Track your delivery</a></p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/auth/login.blade.php ENDPATH**/ ?>