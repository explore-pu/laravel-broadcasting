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

                        <template v-for="chat in chats" :key="chat.id">
                            <button class="h-14 p-4 w-full flex items-center relative hover:bg-gray-50"
                                    :ref="(el) => chatRefs[chat.id] = el"
                                    @click="friendTarget(chat)"
                                    v-if="chat.friend !== null"
                            >
                                <img :src="friendAvatar(chat.friend.avatar)" alt="avatar" class="h-9">
                                <span class="ml-2 line-clamp-1" :title="chat.friend.name">{{ chat.friend.name }}</span>
                                <IconCircle class="z-10 w-3 h-3 text-red-600 absolute top-1 left-11 hidden"/>
                            </button>
                            <button class="h-14 p-4 w-full flex items-center relative hover:bg-gray-50"
                                    :ref="(el) => chatRefs[chat.id] = el"
                                    @click="groupTarget(chat)"
                                    v-else-if="chat.group !== null"
                            >
                                <img :src="groupAvatar(chat.group.avatar)" alt="avatar" class="h-9">
                                <span class="ml-2 line-clamp-1" :title="chat.group.name">{{ chat.group.name }}</span>
                                <IconCircle class="z-10 w-3 h-3 text-red-600 absolute top-1 left-11 hidden"/>
                            </button>
                        </template>
                    </div>
                    <div class="w-full relative" v-show="loading">
                        <div class="header w-full h-14 absolute top-0 border-b p-5 bg-white flex items-baseline z-10">
                            <div ref="receiverRef"></div>
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
                        <div class="flex items-center gap-2 px-6 py-2 hover:bg-gray-300"
                             :ref="(el) => friendRefs[friend.id] = el"
                             @click="selectMember($event, friend.id)"
                             v-for="friend in searchFriends"
                             :key="friend.id"
                        >
                            <input type="checkbox" class="rounded-full" :value="friend.id" @change="changeMember">
                            <img class="h-9" :src="friendAvatar(friend.avatar)" alt="">
                            <span class="line-clamp-1">{{ friend.name }}</span>
                        </div>
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
                                        <img :src="friendAvatar(checkedFriend.avatar)" alt="avatar" class="h-14">
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
                                :disabled="checkedFriends.length === 0" @click="doneMembers"
                        >
                            完成
                        </button>
                        <button type="button" class="px-8 py-1 bg-gray-200 text-green-500" @click="cancelMembers">取消</button>
                    </div>
                </div>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>

<script setup>
import {nextTick, onMounted, ref, watch, computed} from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, router} from '@inertiajs/vue3';
import IconCircle from "@/Components/Icons/icon-circle.vue";
import IconXmark from "@/Components/Icons/icon-xmark.vue";
import IconPlus from "@/Components/Icons/icon-plus.vue";
import Dialog from "@/Components/Dialog.vue";
import DefaultFriendAvatar from "@/Assets/defaultAvatar.png";
import DefaultGroupAvatar from "@/Assets/defaultGroup.jpg";

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

const loading = ref(false);
const chatRefs = ref([]);
const receiverRef = ref();
const friendRefs = ref([]);

const chats = ref(props.chats);
const messages = ref([]);
const message = ref("");
const currentChat = ref({});
const messagesContainer = ref(null);
const isFriendTyping = ref(false);
const isFriendTypingTimer = ref(null);
const localChat = ref(localStorage.getItem('chat'));
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

const friendAvatar = computed(() => (src) => {
    return src ?? DefaultFriendAvatar;
});

const groupAvatar = computed(() => (src) => {
    return src ?? DefaultGroupAvatar;
});

const sendMessage = (event) => {
    if (message.value.trim() !== "") {
        const type = currentChat.value.group !== null ? 'group' : 'friend';
        const receiverId = currentChat.value.group !== null ? currentChat.value.group_id : currentChat.value.friend_id;

        axios.post(`/${type}/${receiverId}/message`, {message: message.value}).then((response) => {
            messages.value.push(response.data);
            message.value = "";
        });
    }
};

const sendTypingEvent = () => {
    if (currentChat.value.friend !== null) {
        Echo.private(`friend.${currentChat.value.friend_id}`).whisper("typing", {
            user_id: props.auth.user.id,
            friend_id: currentChat.value.friend_id,
        });
    }
};

const toggleTarget = (chat) => {
    loading.value = true;

    if (chatRefs.value[localChat.value]) {
        chatRefs.value[localChat.value].classList.remove('bg-gray-200');
        chatRefs.value[localChat.value].classList.add('hover:bg-gray-50');
    }

    chatRefs.value[chat.id].classList.add('bg-gray-200');
    chatRefs.value[chat.id].classList.remove('hover:bg-gray-50');
    chatRefs.value[chat.id].lastChild.classList.add('hidden');

    localStorage.setItem('chat', chat.id);
    localChat.value = chat.id;
    currentChat.value = chat;
}

const friendTarget = (chat) => {
    toggleTarget(chat);

    axios.get(`/friend/${chat.friend.id}/messages`).then((response) => {
        console.log(response);
        receiverRef.value.innerText = chat.friend.name;
        messages.value = response.data.messages;
    });
}

const groupTarget = (chat) => {
    toggleTarget(chat);

    axios.get(`/group/${chat.group.id}/messages`).then((response) => {
        console.log(response);
        receiverRef.value.innerText = chat.group.name + '（' + response.data.members.length + '）';
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

const selectMember = (event, id) => {
    if (event.target !== friendRefs.value[id].firstChild) {
        friendRefs.value[id].firstChild.click();
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
    friendRefs.value[id].firstChild.click();
}

const doneMembers = () => {
    axios.post(`/group/create`, {members: checkedFriends.value.map(friend => ({ id: friend.id, name: friend.name }))}).then((response) => {
        chats.value.unshift(response.data);
        echoGroup(response.data.group_id);

        cancelMembers();
    });
}

const cancelMembers = () => {
    showDialog.value = false;
    searchFriends.value = [];
    checkedFriends.value = [];
}

const echoGroup = (groupId) => {
    Echo.private(`group.${groupId}`)
        .listen("MessageGroupSent", (response) => {
            console.log('MessageGroupSent:', response.message);

            const chat = chats.value.filter(chat => parseInt(chat.group_id) === parseInt(response.message.group_id));
            if (parseInt(chat[0].id) === parseInt(localChat.value)) {
                if (parseInt(response.message.user_id) !== parseInt(props.auth.user.id)) {
                    messages.value.push(response.message);
                }
                clickTarget();
            }
            else {
                chatRefs.value[chat[0].id].lastChild.classList.remove('hidden');
            }
        });
}

const clickTarget = () => {
    if (chatRefs.value[localChat.value]) {
        chatRefs.value[localChat.value].click();
    }
}

onMounted(() => {
    console.info('chats：', chats);

    clickTarget();

    Echo.private(`chat.${props.auth.user.id}`)
        .listen("ChatSent", (response) => {
            console.info('ChatSent:', response)

            const chat = chats.value.filter(chat => {
                return chat.user_id === parseInt(response.chat.user_id) && (
                    (chat.friend_id !== null && response.chat.friend_id !== null && parseInt(chat.friend_id) === parseInt(response.chat.friend_id)) ||
                    (chat.group_id !== null && response.chat.group_id !== null && parseInt(chat.group_id) === parseInt(response.chat.group_id))
                )
            });

            if (chat.length === 0) {
                chats.value.unshift(response.chat);
                if (response.chat.group !== null) {
                    echoGroup(response.chat.group.id);
                }
            }
        });

    Echo.private(`friend.${props.auth.user.id}`)
        .listen("MessageFriendSent", (response) => {
            console.log('MessageFriendSent:', response.message);
            const chat = chats.value.filter(chat => parseInt(chat.friend_id) === parseInt(response.message.user_id));

            if (parseInt(chat[0].id) === parseInt(localChat.value)) {
                messages.value.push(response.message);
                clickTarget();
            }
            else {
                chatRefs.value[chat[0].id].lastChild.classList.remove('hidden');
            }
        })
        .listenForWhisper("typing", (response) => {
            isFriendTyping.value = parseInt(response.user_id) === parseInt(currentChat.value.friend_id) && parseInt(response.friend_id) === parseInt(props.auth.user.id);

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
