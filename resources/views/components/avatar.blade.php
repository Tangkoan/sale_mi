<div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden border border-border-color">
    <template x-if="user.avatar">
        <img :src="'/storage/' + user.avatar" class="w-full h-full object-cover">
    </template>
    <template x-if="!user.avatar">
        <span x-text="user.name.charAt(0)"></span>
    </template>
</div>