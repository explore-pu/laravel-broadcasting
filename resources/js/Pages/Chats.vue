<template>
    <Head title="Chat"/>

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm h-screen max-h-184 flex">
                    <div class="w-80 flex-none border-r">
                        <div class="h-14 p-4 border-b flex justify-between items-center gap-2">
                            <div class="w-full relative flex items-center">
                                <input class="w-full h-8 border-gray-300 rounded-md px-2 text-sm" type="text"
                                       placeholder="Search">
                                <button class="absolute right-1 hidden">
                                    <IconXmark class="w-5 h-5"/>
                                </button>
                            </div>
                            <button type="button" title="Start a group chat">
                                <IconPlus class="w-5 h-5" @click="newGroup"/>
                            </button>
                        </div>

                        <template v-for="chat in chats">
                            <button class="h-14 p-4 w-full hover:bg-gray-50 flex items-center relative"
                                    :data-friend="chat.friend.id" v-if="chat.friend !== null"
                                    @click="friendTarget($event, chat.friend)">
                                <img :src="chat.friend.avatar" alt="avatar" class="h-9">
                                <span class="ml-2 line-clamp-1" :title="chat.friend.name">{{ chat.friend.name }}</span>
                                <IconCircle class="z-10 w-3 h-3 text-red-600 absolute top-1 left-11 hidden"/>
                            </button>
                            <button class="h-14 p-4 w-full hover:bg-gray-50 flex items-center relative"
                                    :data-group="chat.group.id" v-else-if="chat.group !== null"
                                    @click="groupTarget($event, chat.group)">
                                <img :src="chat.group.avatar" alt="avatar" class="h-9">
                                <span class="ml-2 line-clamp-1" :title="chat.group.name">{{ chat.group.name }}</span>
                                <IconCircle class="z-10 w-3 h-3 text-red-600 absolute top-1 left-11 hidden"/>
                            </button>
                        </template>
                    </div>
                    <div class="box w-full relative hidden">
                        <div class="header w-full h-14 absolute top-0 border-b p-5 bg-white flex items-baseline z-10">
                            <div class="receiver-name"></div>
                            <div class="text-xs text-gray-700 ml-2" v-if="isFriendTyping">Typing...</div>
                        </div>
                        <div ref="messagesContainer" class="mt-16 p-2 h-140 overflow-auto">
                            <template v-for="message in messages" :key="message.id">
                                <div class="flex items-center mb-2" v-if="message.user_id === $page.props.auth.user.id">
                                    <div class="p-2 ml-auto">
                                        <div class="text-right" v-if="'user' in message">{{ message.user.name }}</div>
                                        <div class="p-2 rounded-lg max-w-3xl bg-blue-500 text-white">{{
                                                message.text
                                            }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center mb-2" v-else>
                                    <div class="p-2 mr-auto">
                                        <div class="text-left" v-if="'user' in message">{{ message.user.name }}</div>
                                        <div class="p-2 rounded-lg max-w-3xl bg-gray-200">{{ message.text }}</div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="w-full absolute bottom-0 border-t bg-white z-10">
                            <textarea v-model="message"
                                      @keydown="sendTypingEvent"
                                      @keyup.enter="sendMessage"
                                      class="w-full border-none outline-none resize-none focus:ring-0"
                                      rows="2"
                                      placeholder="Enter a message..."
                            >
                            </textarea>
                            <button type="button" class="float-right bg-blue-500 text-white w-24 m-2 rounded"
                                    @click="sendMessage">Send
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Dialog :show="showDialog" @update:show="showDialog = $event" size="2xl">
            <div class="h-full grid grid-cols-2">
                <div class="border-r">
                    <div class="px-6 py-2 h-12">
                        <input type="text" class="h-8 w-full text-sm text-gray-300 border-gray-300 rounded" placeholder="搜索" />
                    </div>
                    <div class="overflow-auto h-120">
                        <template v-for="friend in searchFriends" :key="friend.id">
                            <div class="friend flex items-center gap-2 px-6 py-2 hover:bg-gray-300" @click="selectMember">
                                <input type="checkbox" class="rounded-full" :value="friend.id" @change="changeMember">
                                <img class="h-9" :src="friend.avatar" alt="">
                                <span class="line-clamp-1">{{ friend.name }}</span>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="px-6">
                    <div class="h-12 flex justify-between items-center">
                        <p class="text-sm">选择联系人</p>
                        <p class="text-xs text-gray-500">已选择{{ checkedFriends.length }}个联系人</p>
                    </div>
                    <div class="overflow-auto h-120">
                        <div class="grid grid-cols-3">
                            <template v-for="checkedFriend in checkedFriends" :key="checkedFriend.id">
                                <div class="w-full flex justify-center">
                                    <div class="h-24 w-14 m-1 flex flex-col items-center relative">
                                        <img :src="checkedFriend.avatar" alt="avatar" class="h-14">
                                        <p class="flex-none line-clamp-1 text-xs" >{{ checkedFriend.name }}</p>
                                        <button class="absolute -top-1 -right-1 bg-gray-300 rounded-full" @click="deleteMember(checkedFriend.id)">
                                            <IconXmark class="w-4 h-4 p-0.5"/>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="flex justify-around py-4">
                        <button type="button" class="px-8 py-1"
                                :class="checkedFriends.length === 0 ? 'bg-gray-200 text-gray-400' : 'bg-green-500 text-white'"
                                :disabled="checkedFriends.length === 0" @click="showDialog = false"
                        >
                            完成
                        </button>
                        <button type="button" class="px-8 py-1 bg-gray-200 text-green-500" @click="showDialog = false">取消</button>
                    </div>
                </div>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>

<script setup>
import {nextTick, onMounted, ref, watch} from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import IconCircle from "@/Components/Icons/icon-circle.vue";
import IconXmark from "@/Components/Icons/icon-xmark.vue";
import IconPlus from "@/Components/Icons/icon-plus.vue";
import Dialog from "@/Components/Dialog.vue";

const props = defineProps({
    chats: {
        type: Array,
        required: true,
    },
    auth: {
        type: Object,
        required: true,
    },
});

const chats = ref(props.chats);
const messages = ref([]);
const message = ref("");
const currentMessages = ref({});
const messagesContainer = ref(null);
const isFriendTyping = ref(false);
const isFriendTypingTimer = ref(null);
const chatType = localStorage.getItem('chatType');
const chatId = localStorage.getItem('chatId');
const showDialog = ref(false);
const friends = ref([]);
const searchFriends = ref([]);
const checkedFriends = ref([]);

watch(
    messages,
    () => {
        nextTick(() => {
            messagesContainer.value.scrollTo({
                top: messagesContainer.value.scrollHeight,
                behavior: "smooth",
            });
        });
    },
    {deep: true}
);

const sendMessage = (event) => {
    // 阻止默认行为并防止换行
    event.preventDefault();

    if (message.value.trim() !== "") {
        axios.post(`/messages/${currentMessages.type}/${currentMessages.receiver.id}`, {message: message.value}).then((response) => {
            messages.value.push(response.data);
            message.value = "";
        });
    }
};

const sendTypingEvent = () => {
    if (currentMessages.type === 'friend') {
        Echo.private(`friend.${currentMessages.receiver.id}`).whisper("typing", {
            type: currentMessages.type,
            user_id: props.auth.user.id,
            friend_id: currentMessages.receiver.id,
        });
    }
};

const toggleTarget = (event) => {
    const boxDom = document.querySelector('.box.hidden');
    if (boxDom !== null) {
        boxDom.classList.remove('hidden');
    }

    const activeChatDom = document.querySelector('button.bg-gray-200');
    if (activeChatDom !== null) {
        activeChatDom.classList.remove('bg-gray-200');
        activeChatDom.classList.add('hover:bg-gray-50');
    }

    event.target.classList.add('bg-gray-200');
    event.target.classList.remove('hover:bg-gray-50');
    event.target.lastChild.classList.add('hidden');
}

const friendTarget = (event, friend) => {
    toggleTarget(event);

    currentMessages.type = 'friend';
    currentMessages.receiver = friend;

    localStorage.setItem('chatType', 'friend');
    localStorage.setItem('chatId', friend.id);

    axios.get(`/messages/friend/${friend.id}`).then((response) => {
        console.log(response);

        const boxHeaderDom = document.querySelector('.receiver-name');
        boxHeaderDom.innerText = friend.name;
        messages.value = response.data.messages;
    });
}

const groupTarget = (event, group) => {
    toggleTarget(event);

    currentMessages.type = 'group';
    currentMessages.receiver = group;

    localStorage.setItem('chatType', 'group');
    localStorage.setItem('chatId', group.id);

    axios.get(`/messages/group/${group.id}`).then((response) => {
        console.log(response);
        const boxHeaderDom = document.querySelector('.receiver-name');
        boxHeaderDom.innerText = group.name + '（' + response.data.members.length + '）';
        messages.value = response.data.messages;
    });
}

const newGroup = () => {
    showDialog.value = true;

    axios.get(`/friends`).then((response) => {
        friends.value = response.data.friends;
        searchFriends.value = response.data.friends;
    });
}

const selectMember = (event) => {
    let friendDom;
    if (event.target.classList.contains('friend')) {
        friendDom = event.target;
    } else {
        friendDom = event.target.parentElement;
    }
    if (event.target !== friendDom.firstChild) {
        friendDom.firstChild.click();
    }
}

const changeMember = (event) => {
    if (event.target.checked) {
        const friend = friends.value.filter(friend => friend.id === parseInt(event.target.value))[0];
        checkedFriends.value.push(friend);
    } else {
        checkedFriends.value = checkedFriends.value.filter(friend => friend.id !== parseInt(event.target.value));
    }
}

const deleteMember = (id) => {
    document.querySelector(`.friend [value="${id}"]`).click();
}

const echoGroup = (groupId) => {
    Echo.private(`group.${groupId}`)
        .listen("MessageGroupSent", (response) => {
            console.log('接收的群组消息：', response.message);
            if (currentMessages.type === 'group' && parseInt(response.message.group_id) === currentMessages.receiver.id && parseInt(response.message.user_id) !== props.auth.user.id) {
                messages.value.push(response.message);
            } else if (currentMessages.type !== 'group' || parseInt(response.message.group_id) !== currentMessages.receiver.id) {
                const chatDom = document.querySelector(`[data-group="${response.message.group_id}"]`);
                chatDom.lastChild.classList.remove('hidden');
            }
        });
}

onMounted(() => {
    console.info('chats：', chats);

    const chatDom = document.querySelector(`[data-${chatType}="${chatId}"]`);
    if (chatDom) {
        chatDom.click();
    }

    Echo.private(`chat.${props.auth.user.id}`)
        .listen("ChatSent", (response) => {
            const chat = chats.value.filter(chat => chat.user_id === response.chat.user_id && (chat.friend_id === parseInt(response.chat.friend_id) || chat.group_id === parseInt(response.chat.group_id)));

            if (chat.length === 0) {
                chats.value.unshift(response.chat);
                if (response.chat.group !== null) {
                    echoGroup(response.chat.group.id);
                }
            }
        });

    Echo.private(`friend.${props.auth.user.id}`)
        .listen("MessageFriendSent", (response) => {
            console.log('接收的朋友消息：', response.message);
            if (currentMessages.type === 'friend' && parseInt(response.message.friend_id) === props.auth.user.id && parseInt(response.message.user_id) === currentMessages.receiver.id) {
                messages.value.push(response.message);
            } else if (currentMessages.type !== 'friend' || parseInt(response.message.user_id) !== currentMessages.receiver.id) {
                const chatDom = document.querySelector(`[data-friend="${response.message.user_id}"]`);
                chatDom.lastChild.classList.remove('hidden');
            }
        })
        .listenForWhisper("typing", (response) => {
            isFriendTyping.value = response.type === currentMessages.type && response.user_id === currentMessages.receiver.id && response.friend_id === props.auth.user.id;

            if (isFriendTypingTimer.value) {
                clearTimeout(isFriendTypingTimer.value);
            }

            isFriendTypingTimer.value = setTimeout(() => {
                isFriendTyping.value = false;
            }, 1000);
        });

    chats.value.forEach(function (chat) {
        if (chat.group !== null) {
            echoGroup(chat.group.id);
        }
    });
});
</script>
