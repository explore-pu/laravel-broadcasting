<script setup>
import {onMounted, ref} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head, router} from "@inertiajs/vue3";
import IconMars from "@/Components/Icons/icon-mars.vue";
import IconVenus from "@/Components/Icons/icon-venus.vue";
import IconXmark from "@/Components/Icons/icon-xmark.vue";

const props = defineProps({
    contacts: {
        type: Object,
        required: true,
    },
});

const newFriends = ref([]);
const groups = ref([]);
const friends = ref([]);
const newFriendsRef = ref();
const groupDetailsRef = ref();
const friendDetailsRef = ref();
const groupMembers = ref([]);
const currentGroup = ref([]);
const currentFriend = ref([]);

console.log(props.contacts);

const handleSearchFocus = (event) => {
    event.target.placeholder = '';
    event.target.nextElementSibling.classList.remove('hidden');
}

const handleCancelSearch = (event) => {
    const buttonElement = event.target.closest('button');
    buttonElement.classList.add('hidden');
    buttonElement.previousElementSibling.placeholder = 'Search';
    buttonElement.previousElementSibling.value = '';
}

const toggleTarget = (event) => {
    const activeChatDom = document.querySelector('button.bg-gray-200');
    if (activeChatDom !== null) {
        activeChatDom.classList.remove('bg-gray-200');
        activeChatDom.classList.add('hover:bg-gray-50');
    }

    event.target.classList.add('bg-gray-200');
    event.target.classList.remove('hover:bg-gray-50');
}

const handleNewFriend = (event) => {
    toggleTarget(event);

    groupDetailsRef.value.classList.add('hidden');
    friendDetailsRef.value.classList.add('hidden');
    newFriendsRef.value.classList.remove('hidden');
}

const handleGroupDetail = (event, group) => {
    toggleTarget(event);

    currentGroup.value = group;
    groupMembers.value = group.members;
    document.querySelector('.group-name').innerText = group.name + '（' + group.members.length + '）';

    newFriendsRef.value.classList.add('hidden');
    friendDetailsRef.value.classList.add('hidden');
    groupDetailsRef.value.classList.remove('hidden');
}

const handleFriendDetail = (event, Friend) => {
    toggleTarget(event);

    currentFriend.value = Friend;
    console.log(currentFriend)

    newFriendsRef.value.classList.add('hidden');
    groupDetailsRef.value.classList.add('hidden');
    friendDetailsRef.value.classList.remove('hidden');
}

const sendGroupMessage = () => {
    axios.post(`/chat`, {group_id: currentGroup.value.id}).then((response) => {
        localStorage.setItem('chatType', 'group');
        localStorage.setItem('chatId', currentGroup.value.id);
        router.get('/');
    });
}

const sendFriendMessage = () => {
    axios.post(`/chat`, {friend_id: currentFriend.value.id}).then((response) => {
        localStorage.setItem('chatType', 'friend');
        localStorage.setItem('chatId', currentFriend.value.id);
        router.get('/');
    });
}

onMounted(() => {
    newFriends.value = props.contacts.friend_requests;
    groups.value = props.contacts.groups;
    friends.value = props.contacts.friends;
});

</script>

<template>
    <Head title="Contacts"/>

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm h-screen max-h-184 flex">
                    <div class="w-80 flex-none border-r">
                        <div class="h-16 p-4 border-b flex justify-between items-center gap-2">
                            <div class="w-full relative flex items-center">
                                <input @focus="handleSearchFocus($event)"
                                       class="w-full h-8 border-gray-300 focus:outline-none focus:ring-0 rounded-md px-2 text-sm"
                                       type="text" placeholder="Search">
                                <button class="absolute right-1 hidden" @click="handleCancelSearch($event)">
                                    <IconXmark class="w-5 h-5"/>
                                </button>
                            </div>
                        </div>
                        <button class="h-16 p-4 bg-gray-200 border-b w-full text-left" @click="handleNewFriend($event)">
                            New friends
                        </button>
                        <div>
                            <div class="border-b">
                                <p class="text-sm px-4 py-2">Group chats</p>
                                <template v-for="group in groups" :key="group.id">
                                    <button class="h-16 p-4 hover:bg-gray-50 w-full flex items-center"
                                            @click="handleGroupDetail($event, group)">
                                        <img :src="group.avatar" alt="avatar" class="h-9">
                                        <span class="ml-2 line-clamp-1" :title="group.name">{{ group.name }}</span>
                                    </button>
                                </template>
                            </div>
                            <template v-for="friend in friends" :key="friend.id">
                                <button class="h-16 p-4 hover:bg-gray-50 w-full flex items-center"
                                        @click="handleFriendDetail($event, friend)">
                                    <img :src="friend.avatar" alt="avatar" class="h-9">
                                    <span class="ml-2 line-clamp-1" :title="friend.name">{{ friend.name }}</span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <div class="box w-full relative">
                        <div class="mt-16 h-168 overflow-auto">
                            <div ref="newFriendsRef" class="new-friends">
                                <div class="header w-full h-16 absolute top-0 border-b p-5 bg-white">New friends</div>
                                <div class="max-w-2xl mx-auto">
                                    <template v-for="newFriend in newFriends" :key="newFriend.id">
                                        <div class="h-20 py-4 border-b w-full flex justify-between items-center">
                                            <div class="flex items-center max-w-lg">
                                                <img :src="newFriend.avatar" alt="avatar" class="h-12">
                                                <div class="mx-2">
                                                    <p class="mb-1">{{ newFriend.name }}</p>
                                                    <p class="mb-1 text-xs text-gray-400 line-clamp-1"
                                                       :title="newFriend.pivot.message">{{
                                                            newFriend.pivot.message
                                                        }}</p>
                                                </div>
                                            </div>

                                            <div class="flex gap-2">
                                                <button type="button"
                                                        class="px-2 py-0.5 rounded bg-green-500 text-white"
                                                        v-if="newFriend.pivot.status === 0">accept
                                                </button>
                                                <p class="text-green-500" v-if="newFriend.pivot.status === 1">added</p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div ref="groupDetailsRef" class="group-details hidden">
                                <div class="header w-full h-16 absolute top-0 border-b p-5 bg-white">
                                    <div class="group-name"></div>
                                </div>
                                <div class="px-16 py-8 grid grid-cols-8 gap-4">
                                    <template v-for="member in groupMembers" :key="member.id">
                                        <div class="h-28 p-2 flex flex-col items-center">
                                            <img :src="member.avatar" alt="avatar" class="h-16">
                                            <p class="flex-none line-clamp-1" :title="member.name">{{ member.name }}</p>
                                        </div>
                                    </template>
                                </div>

                                <div
                                    class="w-full h-20 absolute bottom-0 bg-white z-10 flex justify-center items-center">
                                    <button type="button" class="bg-blue-500 text-white w-36 p-2 rounded"
                                            @click="sendGroupMessage">Send message
                                    </button>
                                </div>
                            </div>
                            <div ref="friendDetailsRef" class="friend-details hidden">
                                <div class="max-w-xs mx-auto flex justify-center items-center">
                                    <div class="h-24 py-4 border-b w-full flex items-center">
                                        <img :src="currentFriend.avatar" alt="avatar" class="w-20">
                                        <span class="ml-4">{{ currentFriend.name }}</span>
                                        <IconMars class="text-blue-600" v-if="currentFriend.gender === 1"/>
                                        <IconVenus class="text-pink-500" v-if="currentFriend.gender === 2"/>
                                    </div>
                                </div>
                                <div class="max-w-xs mx-auto flex justify-center items-center">
                                    <div class="h-18 py-4 border-b w-full flex">
                                        <span class="w-20">Email</span>
                                        <span class="ml-4 text-gray-400">{{ currentFriend.email }}</span>
                                    </div>
                                </div>

                                <div class="max-w-xs mx-auto flex justify-center items-center">
                                    <div class="h-18 py-4 border-b w-full flex">
                                        <span class="w-20">Signature</span>
                                        <span class="ml-4 text-gray-400">{{ currentFriend.signature }}</span>
                                    </div>
                                </div>

                                <div
                                    class="w-full h-20 absolute bottom-0 bg-white z-10 flex justify-center items-center">
                                    <button type="button" class="bg-blue-500 text-white w-36 p-2 rounded"
                                            @click="sendFriendMessage">Send message
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
