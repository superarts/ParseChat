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

#import <AddressBook/AddressBook.h>
#import <MessageUI/MFMailComposeViewController.h>
#import <MessageUI/MFMessageComposeViewController.h>

#import <Parse/Parse.h>
#import "ProgressHUD.h"

#import "AppConstant.h"
#import "messages.h"
#import "utilities.h"

#import "PrivateView.h"
#import "ChatView.h"
#import "SearchView.h"
#import "NavigationController.h"

//-------------------------------------------------------------------------------------------------------------------------------------------------
@interface PrivateView()
{
	NSMutableArray *users1;
	NSMutableArray *users2;

	NSIndexPath *indexSelected;
}
@end
//-------------------------------------------------------------------------------------------------------------------------------------------------

@implementation PrivateView

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
	if (self)
	{
		[self.tabBarItem setImage:[UIImage imageNamed:@"tab_private"]];
		self.tabBarItem.title = @"联系人";
		//-----------------------------------------------------------------------------------------------------------------------------------------
		[[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(actionCleanup) name:NOTIFICATION_USER_LOGGED_OUT object:nil];
	}
	return self;
}

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)viewDidLoad
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	[super viewDidLoad];
	self.title = @"联系人";
	//---------------------------------------------------------------------------------------------------------------------------------------------
	self.navigationItem.rightBarButtonItem = [[UIBarButtonItem alloc] initWithTitle:@"搜索" style:UIBarButtonItemStylePlain target:self
																			 action:@selector(actionSearch)];
	//---------------------------------------------------------------------------------------------------------------------------------------------
	users1 = [[NSMutableArray alloc] init];
	users2 = [[NSMutableArray alloc] init];
}

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)viewDidAppear:(BOOL)animated
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	[super viewDidAppear:animated];
	//---------------------------------------------------------------------------------------------------------------------------------------------
	if ([PFUser currentUser] != nil)
	{
		ABAddressBookRef addressBook = ABAddressBookCreateWithOptions(NULL, nil);
		ABAddressBookRequestAccessWithCompletion(addressBook, ^(bool granted, CFErrorRef error)
		{
			dispatch_async(dispatch_get_main_queue(), ^{
				if (granted) [self loadUsers1];
			});
		});
	}
	else LoginUser(self);
}

#pragma mark - User actions

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)actionSearch
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	SearchView *searchView = [[SearchView alloc] init];
	NavigationController *navController = [[NavigationController alloc] initWithRootViewController:searchView];
	[self presentViewController:navController animated:YES completion:nil];
}

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)actionCleanup
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	[users1 removeAllObjects];
	[users2 removeAllObjects];
	[self.tableView reloadData];
}

#pragma mark - Backend methods

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)loadUsers1
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	if (ABAddressBookGetAuthorizationStatus() == kABAuthorizationStatusAuthorized)
	{
		CFErrorRef *error = NULL;
		ABAddressBookRef addressBook = ABAddressBookCreateWithOptions(NULL, error);
		ABRecordRef sourceBook = ABAddressBookCopyDefaultSource(addressBook);
		CFArrayRef allPeople = ABAddressBookCopyArrayOfAllPeopleInSourceWithSortOrdering(addressBook, sourceBook, kABPersonFirstNameProperty);
		CFIndex personCount = CFArrayGetCount(allPeople);

		[users1 removeAllObjects];
		for (int i=0; i<personCount; i++)
		{
			ABMultiValueRef tmp;
			ABRecordRef person = CFArrayGetValueAtIndex(allPeople, i);

			NSString *first = @"";
			tmp = ABRecordCopyValue(person, kABPersonFirstNameProperty);
			if (tmp != nil) first = [NSString stringWithFormat:@"%@", tmp];

			NSString *last = @"";
			tmp = ABRecordCopyValue(person, kABPersonLastNameProperty);
			if (tmp != nil) last = [NSString stringWithFormat:@"%@", tmp];

			NSMutableArray *emails = [[NSMutableArray alloc] init];
			ABMultiValueRef multi1 = ABRecordCopyValue(person, kABPersonEmailProperty);
			for (CFIndex j=0; j<ABMultiValueGetCount(multi1); j++)
			{
				tmp = ABMultiValueCopyValueAtIndex(multi1, j);
				if (tmp != nil) [emails addObject:[NSString stringWithFormat:@"%@", tmp]];
			}

			NSMutableArray *phones = [[NSMutableArray alloc] init];
			ABMultiValueRef multi2 = ABRecordCopyValue(person, kABPersonPhoneProperty);
			for (CFIndex j=0; j<ABMultiValueGetCount(multi2); j++)
			{
				tmp = ABMultiValueCopyValueAtIndex(multi2, j);
				if (tmp != nil) [phones addObject:[NSString stringWithFormat:@"%@", tmp]];
			}

			NSString *name = [NSString stringWithFormat:@"%@ %@", first, last];
			[users1 addObject:@{@"name":name, @"emails":emails, @"phones":phones}];
		}
		CFRelease(allPeople);
		CFRelease(addressBook);
		[self loadUsers2];
	}
}

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)loadUsers2
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	NSMutableArray *emails = [[NSMutableArray alloc] init];
	for (NSDictionary *user in users1)
	{
		for (NSString *email in user[@"emails"])
		{
			[emails addObject:email];
		}
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------
	PFQuery *query = [PFQuery queryWithClassName:PF_USER_CLASS_NAME];
	[query whereKey:PF_USER_OBJECTID notEqualTo:[PFUser currentUser].objectId];
	[query whereKey:PF_USER_EMAILCOPY containedIn:emails];
	[query orderByAscending:PF_USER_FULLNAME];
	[query setLimit:1000];
	[query findObjectsInBackgroundWithBlock:^(NSArray *objects, NSError *error)
	{
		if (error == nil)
		{
			[users2 removeAllObjects];
			for (PFUser *user in objects)
			{
				[users2 addObject:user];
				[self removeFromUser1:user[PF_USER_EMAILCOPY]];
			}
			[self.tableView reloadData];
		}
		else [ProgressHUD showError:@"网络错误，请重试"];
	}];
}

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)removeFromUser1:(NSString *)email_
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	NSMutableArray *remove = [[NSMutableArray alloc] init];
	//---------------------------------------------------------------------------------------------------------------------------------------------
	for (NSDictionary *user in users1)
	{
		for (NSString *email in user[@"emails"])
		{
			if ([email isEqualToString:email_])
			{
				[remove addObject:user];
			}
		}
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------
	for (NSDictionary *user in remove)
	{
		[users1 removeObject:user];
	}
}

#pragma mark - Table view data source

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	return 2;
}

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	if (section == 0) return [users2 count];
	if (section == 1) return [users1 count];
	return 0;
}

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (NSString *)tableView:(UITableView *)tableView titleForHeaderInSection:(NSInteger)section
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	if ((section == 0) && ([users2 count] != 0)) return @"已注册用户";
	if ((section == 1) && ([users1 count] != 0)) return @"未注册用户";
	return nil;
}

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	UITableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:@"cell"];
	if (cell == nil) cell = [[UITableViewCell alloc] initWithStyle:UITableViewCellStyleSubtitle reuseIdentifier:@"cell"];
	//---------------------------------------------------------------------------------------------------------------------------------------------
	if (indexPath.section == 0)
	{
		PFUser *user = users2[indexPath.row];
		cell.textLabel.text = user[PF_USER_FULLNAME];
		cell.detailTextLabel.text = user[PF_USER_EMAILCOPY];
	}
	if (indexPath.section == 1)
	{
		NSDictionary *user = users1[indexPath.row];
		NSString *email = [user[@"emails"] firstObject];
		NSString *phone = [user[@"phones"] firstObject];
		cell.textLabel.text = user[@"name"];
		cell.detailTextLabel.text = (email != nil) ? email : phone;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------
	cell.detailTextLabel.textColor = [UIColor lightGrayColor];
	return cell;
}

#pragma mark - Table view delegate

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	[tableView deselectRowAtIndexPath:indexPath animated:YES];
	//---------------------------------------------------------------------------------------------------------------------------------------------
	if (indexPath.section == 0)
	{
		PFUser *user1 = [PFUser currentUser];
		PFUser *user2 = users2[indexPath.row];
		NSString *roomId = StartPrivateChat(user1, user2);
		//-----------------------------------------------------------------------------------------------------------------------------------------
		ChatView *chatView = [[ChatView alloc] initWith:roomId];
		chatView.hidesBottomBarWhenPushed = YES;
		[self.navigationController pushViewController:chatView animated:YES];
	}
	if (indexPath.section == 1)
	{
		indexSelected = indexPath;
		[self inviteUser:users1[indexPath.row]];
	}
}

#pragma mark - Invite helper method

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)inviteUser:(NSDictionary *)user
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	if (([user[@"emails"] count] != 0) && ([user[@"phones"] count] != 0))
	{
		UIActionSheet *action = [[UIActionSheet alloc] initWithTitle:nil delegate:self cancelButtonTitle:@"取消"
											  destructiveButtonTitle:nil otherButtonTitles:@"发送电子邮件邀请", @"发送短信息邀请（推荐）", nil];
		[action showInView:self.view];
	}
	else if (([user[@"emails"] count] != 0) && ([user[@"phones"] count] == 0))
	{
		[self sendMail:user];
	}
	else if (([user[@"emails"] count] == 0) && ([user[@"phones"] count] != 0))
	{
		[self sendSMS:user];
	}
	else [ProgressHUD showError:@"此用户没有相关联系方式"];
}

#pragma mark - UIActionSheetDelegate

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)actionSheet:(UIActionSheet *)actionSheet clickedButtonAtIndex:(NSInteger)buttonIndex
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	if (buttonIndex == actionSheet.cancelButtonIndex) return;
	//---------------------------------------------------------------------------------------------------------------------------------------------
	NSDictionary *user = users1[indexSelected.row];
	if (buttonIndex == 0) [self sendMail:user];
	if (buttonIndex == 1) [self sendSMS:user];
}

#pragma mark - Mail sending method

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)sendMail:(NSDictionary *)user
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	if ([MFMailComposeViewController canSendMail])
	{
		MFMailComposeViewController *mailCompose = [[MFMailComposeViewController alloc] init];
		[mailCompose setToRecipients:user[@"emails"]];
		[mailCompose setSubject:@""];
		[mailCompose setMessageBody:MESSAGE_INVITE isHTML:YES];
		mailCompose.mailComposeDelegate = self;
		[self presentViewController:mailCompose animated:YES completion:nil];
	}
	else [ProgressHUD showError:@"请在系统中设置您的电子邮件"];
    
    PFObject *object = [PFObject objectWithClassName:@"tracking_action"];
    if ([PFUser currentUser]) object[@"user"] = [PFUser currentUser];
    object[@"module"] = @"invite";
    object[@"action"] = @"mail";
    [object saveInBackgroundWithBlock:^(BOOL succeeded, NSError *error) {} ];
}

#pragma mark - MFMailComposeViewControllerDelegate

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)mailComposeController:(MFMailComposeViewController *)controller didFinishWithResult:(MFMailComposeResult)result error:(NSError *)error
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	if (result == MFMailComposeResultSent)
	{
		[ProgressHUD showSuccess:@"邮件发送成功"];
        
        PFObject *object = [PFObject objectWithClassName:@"tracking_action"];
        if ([PFUser currentUser]) object[@"user"] = [PFUser currentUser];
        object[@"module"] = @"invite";
        object[@"action"] = @"mail-success";
        [object saveInBackgroundWithBlock:^(BOOL succeeded, NSError *error) {} ];
	}
	[self dismissViewControllerAnimated:YES completion:nil];
}

#pragma mark - SMS sending method

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)sendSMS:(NSDictionary *)user
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	if ([MFMessageComposeViewController canSendText])
	{
		MFMessageComposeViewController *messageCompose = [[MFMessageComposeViewController alloc] init];
		messageCompose.recipients = user[@"phones"];
		messageCompose.body = MESSAGE_INVITE;
		messageCompose.messageComposeDelegate = self;
		[self presentViewController:messageCompose animated:YES completion:nil];
	}
	else [ProgressHUD showError:@"本设备无法发送短信息"];
    
    PFObject *object = [PFObject objectWithClassName:@"tracking_action"];
    if ([PFUser currentUser]) object[@"user"] = [PFUser currentUser];
    object[@"module"] = @"invite";
    object[@"action"] = @"sms";
    [object saveInBackgroundWithBlock:^(BOOL succeeded, NSError *error) {} ];
}

#pragma mark - MFMessageComposeViewControllerDelegate

//-------------------------------------------------------------------------------------------------------------------------------------------------
- (void)messageComposeViewController:(MFMessageComposeViewController *)controller didFinishWithResult:(MessageComposeResult)result
//-------------------------------------------------------------------------------------------------------------------------------------------------
{
	if (result == MessageComposeResultSent)
	{
		[ProgressHUD showSuccess:@"短信息发送成功"];
        
        PFObject *object = [PFObject objectWithClassName:@"tracking_action"];
        if ([PFUser currentUser]) object[@"user"] = [PFUser currentUser];
        object[@"module"] = @"invite";
        object[@"action"] = @"sms-success";
        [object saveInBackgroundWithBlock:^(BOOL succeeded, NSError *error) {} ];
	}
	[self dismissViewControllerAnimated:YES completion:nil];
}

@end
