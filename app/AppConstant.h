//
// Copyright (c) 2014 Related Code - http://relatedcode.com
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.

//-------------------------------------------------------------------------------------------------------------------------------------------------
#define HEXCOLOR(c) [UIColor colorWithRed:((c>>24)&0xFF)/255.0 green:((c>>16)&0xFF)/255.0 blue:((c>>8)&0xFF)/255.0 alpha:((c)&0xFF)/255.0]

//-------------------------------------------------------------------------------------------------------------------------------------------------
#define		DEFAULT_TAB							0

//-------------------------------------------------------------------------------------------------------------------------------------------------
#define		MESSAGE_INVITE						@"欢迎加入性爱社区参与讨论，传播正确的性知识，成为真正的性爱生活大师！iOS版下载地址："
#define		FORMAT_REPORT						@"举报主题：%@\n内容：%@"
#define     MESSAGE_TOS @"欢迎下载性爱生活大师，并加入我们的社区！为了保证我们的用户拥有一个健康的讨论环境，请遵守以下协议：\n \
\n \
1、您必须年满十八周岁并具有独立民事行为能力\n \
2、严禁粗口、谩骂、人身攻击\n \
3、严禁发布任何约炮类主题\n \
4、严禁发布广告贴、软文\n \
5、严禁在公共主题发布个人联系方式\n \
6、严禁发布违反法律法规的内容\n \
7、严禁发布违反公共道德的内容\n \
\n \
如果您发现有任何不健康内容，请使用右上角“举报”按钮。管理员将删除所有不良言论，严重者直接删除帐号。\n \
\n \
为了营造健康和谐的讨论环境，社区管理员将本着从重从严的标准进行管理操作，如果您不同意以上内容，请点击“拒绝”并卸载本应用。\n \
\n \
非常感谢！"

/*
8、严禁发布涉嫌性别歧视的言论\n \
9、请严格把握健康讨论和色情的界限\n \
10、为避免无谓的争论，到达一定长度的贴子将进行锁帖；请勿重新发布以争论为目的的主题\n \
11、严格禁止对本应用进行反向工程、破解\n \
12、如果转载主题违反了您的利益，请向管理员提交举报信息\n \
13、原创主题版权为本社区所有\n \
 如果情节特别恶劣，并涉及人身攻击、造谣、传播淫秽物品等违法行为，本社区将会积极配合警方举证，对违法行为进行严格打击。\n \
 */

//-------------------------------------------------------------------------------------------------------------------------------------------------
#define		PF_INSTALLATION_CLASS_NAME			@"_Installation"		//	Class name
#define		PF_INSTALLATION_OBJECTID			@"objectId"				//	String
#define		PF_INSTALLATION_USER				@"user"					//	Pointer to User Class

#define		PF_USER_CLASS_NAME					@"_User"				//	Class name
#define		PF_USER_OBJECTID					@"objectId"				//	String
#define		PF_USER_USERNAME					@"username"				//	String
#define		PF_USER_PASSWORD					@"password"				//	String
#define		PF_USER_EMAIL						@"email"				//	String
#define		PF_USER_EMAILCOPY					@"emailCopy"			//	String
#define		PF_USER_FULLNAME					@"fullname"				//	String
#define		PF_USER_FULLNAME_LOWER				@"fullname_lower"		//	String
#define		PF_USER_FACEBOOKID					@"facebookId"			//	String
#define		PF_USER_PICTURE						@"picture"				//	File
#define		PF_USER_THUMBNAIL					@"thumbnail"			//	File

#define		PF_CHAT_CLASS_NAME					@"Chat"					//	Class name
#define		PF_CHAT_USER						@"user"					//	Pointer to User Class
#define		PF_CHAT_ROOMID						@"roomId"				//	String
#define		PF_CHAT_TEXT						@"text"					//	String
#define		PF_CHAT_PICTURE						@"picture"				//	File
#define		PF_CHAT_CREATEDAT					@"createdAt"			//	Date

#define		PF_CHATROOMS_CLASS_NAME				@"ChatRooms"			//	Class name
#define		PF_CHATROOMS_NAME					@"name"					//	String

#define		PF_MESSAGES_CLASS_NAME				@"Messages"				//	Class name
#define		PF_MESSAGES_USER					@"user"					//	Pointer to User Class
#define		PF_MESSAGES_ROOMID					@"roomId"				//	String
#define		PF_MESSAGES_DESCRIPTION				@"description"			//	String
#define		PF_MESSAGES_LASTUSER				@"lastUser"				//	Pointer to User Class
#define		PF_MESSAGES_LASTMESSAGE				@"lastMessage"			//	String
#define		PF_MESSAGES_COUNTER					@"counter"				//	Number
#define		PF_MESSAGES_UPDATEDACTION			@"updatedAction"		//	Date

//-------------------------------------------------------------------------------------------------------------------------------------------------
#define		NOTIFICATION_APP_STARTED			@"NCAppStarted"
#define		NOTIFICATION_USER_LOGGED_IN			@"NCUserLoggedIn"
#define		NOTIFICATION_USER_LOGGED_OUT		@"NCUserLoggedOut"
