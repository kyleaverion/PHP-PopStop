Imports System.Drawing
Imports System.Drawing.Drawing2D
Imports System.Windows.Forms

Public Class TravelHomepageForm
    Inherits Form

    Private mainPanel As Panel
    Private headerPanel As Panel
    Private destinationsPanel As Panel
    Private featuresPanel As Panel
    Private footerPanel As Panel

    Private WithEvents fadeTimer As Timer
    Private WithEvents slideTimer As Timer
    Private currentImageIndex As Integer = 0
    Private slideOffset As Integer = 0
    Private backgroundImages As String() = {"Mountains", "Beach", "City", "Forest", "Desert", "Tropical"}
    Private backgroundColors As Color() = {
        Color.FromArgb(30, 144, 255),
        Color.FromArgb(0, 206, 209),
        Color.FromArgb(138, 43, 226),
        Color.FromArgb(34, 139, 34),
        Color.FromArgb(255, 140, 0),
        Color.FromArgb(255, 20, 147)
    }
    Private user As MainForm.UserInfo

    ' Default constructor
    Public Sub New()
        InitializeComponent()
        SetupUI()
        SetupAnimations()
    End Sub

    ' Constructor with user parameter - FIXED
    Public Sub New(user As MainForm.UserInfo)
        Me.user = user
        InitializeComponent()
        SetupUI()
        SetupAnimations()
        UpdateUIForLoggedInUser()
    End Sub

    Private Sub InitializeComponent()
        Dim resources As System.ComponentModel.ComponentResourceManager = New System.ComponentModel.ComponentResourceManager(GetType(TravelHomepageForm))
        Me.SuspendLayout()
        '
        'TravelHomepageForm
        '
        Me.BackColor = System.Drawing.Color.FromArgb(CType(CType(240, Byte), Integer), CType(CType(248, Byte), Integer), CType(CType(255, Byte), Integer))
        Me.ClientSize = New System.Drawing.Size(1382, 853)
        Me.Font = New System.Drawing.Font("Segoe UI", 10.0!)
        ' Me.Icon = CType(resources.GetObject("$this.Icon"), System.Drawing.Icon) ' Comment this out if you don't have an icon
        Me.MinimumSize = New System.Drawing.Size(1000, 700)
        Me.Name = "TravelHomepageForm"
        Me.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen
        Me.Text = "Lakbay PH Travel & Tours - Discover Amazing Destinations"
        Me.ResumeLayout(False)
    End Sub

    Private Sub SetupUI()
        ' Main container
        mainPanel = New Panel With {
            .Dock = DockStyle.Fill,
            .AutoScroll = True
        }
        Me.Controls.Add(mainPanel)

        CreateHeader()
        CreateDestinationsSection()
        CreateFeaturesSection()
        CreateFooter()
    End Sub

    Private Sub CreateHeader()
        headerPanel = New Panel With {
            .Height = 400,
            .Dock = DockStyle.Top,
            .BackColor = Color.FromArgb(30, 144, 255)
        }

        ' Add gradient background with animation
        AddHandler headerPanel.Paint, AddressOf HeaderPanel_Paint

        ' Logo and title with enhanced styling
        Dim logoLabel As New Label With {
            .Text = "✈️ LAKBAY PH",
            .Font = New Font("Segoe UI", 38, FontStyle.Bold),
            .ForeColor = Color.White,
            .BackColor = Color.Transparent,
            .AutoSize = True,
            .Location = New Point(60, 60)
        }
        headerPanel.Controls.Add(logoLabel)

        Dim subtitleLabel As New Label With {
            .Text = "Discover Your Next Adventure",
            .Font = New Font("Segoe UI", 20, FontStyle.Regular),
            .ForeColor = Color.FromArgb(240, 240, 240),
            .BackColor = Color.Transparent,
            .AutoSize = True,
            .Location = New Point(60, 120)
        }
        headerPanel.Controls.Add(subtitleLabel)

        ' Inspirational message
        Dim inspirationLabel As New Label With {
            .Text = "Explore the world's most breathtaking destinations",
            .Font = New Font("Segoe UI", 16, FontStyle.Italic),
            .ForeColor = Color.FromArgb(220, 220, 220),
            .BackColor = Color.Transparent,
            .AutoSize = True,
            .Location = New Point(60, 160)
        }
        headerPanel.Controls.Add(inspirationLabel)

        ' Enhanced navigation menu
        CreateNavigationMenu()

        ' Add explore button
        Dim exploreButton As New Button With {
            .Text = "🌍 Explore Destinations",
            .Size = New Size(250, 60),
            .Location = New Point(60, 210),
            .BackColor = Color.FromArgb(255, 140, 0),
            .ForeColor = Color.White,
            .Font = New Font("Segoe UI", 14, FontStyle.Bold),
            .FlatStyle = FlatStyle.Flat,
            .Cursor = Cursors.Hand
        }
        exploreButton.FlatAppearance.BorderSize = 0
        AddHandler exploreButton.Click, AddressOf ExploreButton_Click
        AddHandler exploreButton.Paint, AddressOf ExploreButton_Paint
        headerPanel.Controls.Add(exploreButton)

        ' Add sign up button (will be modified if user is logged in)
        Dim signUpButton As New Button With {
            .Text = "👤 Sign Up",
            .Size = New Size(180, 60),
            .Location = New Point(330, 210),
            .BackColor = Color.FromArgb(46, 204, 113),
            .ForeColor = Color.White,
            .Font = New Font("Segoe UI", 14, FontStyle.Bold),
            .FlatStyle = FlatStyle.Flat,
            .Cursor = Cursors.Hand,
            .Tag = "signUpButton" ' Add tag to identify this button
        }
        signUpButton.FlatAppearance.BorderSize = 0
        AddHandler signUpButton.Click, AddressOf SignUpButton_Click
        AddHandler signUpButton.Paint, AddressOf SignUpButton_Paint
        AddHandler signUpButton.MouseEnter, AddressOf SignUpButton_MouseEnter
        AddHandler signUpButton.MouseLeave, AddressOf SignUpButton_MouseLeave
        headerPanel.Controls.Add(signUpButton)

        mainPanel.Controls.Add(headerPanel)
    End Sub

    Private Sub CreateNavigationMenu()
        Dim navPanel As New Panel With {
            .Height = 60,
            .Dock = DockStyle.Bottom,
            .BackColor = Color.FromArgb(20, 20, 20)
        }

        Dim navItems As String() = {"Gallery", "About", "Contact"}
        Dim x As Integer = 60

        For Each item As String In navItems
            Dim navBtn As New Button With {
                .Text = item,
                .Size = New Size(120, 40),
                .Location = New Point(x, 10),
                .BackColor = Color.Transparent,
                .ForeColor = Color.White,
                .FlatStyle = FlatStyle.Flat,
                .Font = New Font("Segoe UI", 11, FontStyle.Regular),
                .Cursor = Cursors.Hand
            }
            navBtn.FlatAppearance.BorderSize = 0
            navBtn.FlatAppearance.MouseOverBackColor = Color.FromArgb(70, 130, 180)

            AddHandler navBtn.Click, AddressOf NavButton_Click
            AddHandler navBtn.MouseEnter, AddressOf NavButton_MouseEnter
            AddHandler navBtn.MouseLeave, AddressOf NavButton_MouseLeave
            navPanel.Controls.Add(navBtn)
            x += 130
        Next

        headerPanel.Controls.Add(navPanel)
    End Sub

    Private Sub CreateDestinationsSection()
        destinationsPanel = New Panel With {
            .Height = 500,
            .Dock = DockStyle.Top,
            .BackColor = Color.White,
            .Padding = New Padding(60)
        }

        Dim titleLabel As New Label With {
            .Text = "Popular Destinations",
            .Font = New Font("Segoe UI", 32, FontStyle.Bold),
            .ForeColor = Color.FromArgb(50, 50, 50),
            .AutoSize = True,
            .Location = New Point(60, 30)
        }
        destinationsPanel.Controls.Add(titleLabel)

        Dim subtitleLabel As New Label With {
            .Text = "Discover amazing places around the world",
            .Font = New Font("Segoe UI", 16, FontStyle.Regular),
            .ForeColor = Color.FromArgb(120, 120, 120),
            .AutoSize = True,
            .Location = New Point(60, 80)
        }
        destinationsPanel.Controls.Add(subtitleLabel)

        ' Destination cards
        Dim destinations As String(,) = {
            {"🗼", "Paris, France", "The City of Light awaits with its romantic charm"},
            {"🏯", "Tokyo, Japan", "Experience the perfect blend of tradition and modernity"},
            {"🗽", "New York, USA", "The city that never sleeps"},
            {"🏰", "London, UK", "Rich history and royal heritage"},
            {"🕌", "Dubai, UAE", "Luxury and innovation in the desert"},
            {"🎭", "Sydney, Australia", "Harbor views and vibrant culture"}
        }

        For i As Integer = 0 To 5
            Dim row As Integer = i \ 3
            Dim col As Integer = i Mod 3

            Dim destinationCard As New Panel With {
                .Size = New Size(320, 180),
                .Location = New Point(60 + (col * 340), 140 + (row * 200)),
                .BackColor = Color.White,
                .Cursor = Cursors.Hand
            }
            AddHandler destinationCard.Paint, AddressOf DestinationCard_Paint
            AddHandler destinationCard.MouseEnter, AddressOf DestinationCard_MouseEnter
            AddHandler destinationCard.MouseLeave, AddressOf DestinationCard_MouseLeave
            AddHandler destinationCard.Click, AddressOf DestinationCard_Click

            Dim iconLabel As New Label With {
                .Text = destinations(i, 0),
                .Font = New Font("Segoe UI", 48),
                .Location = New Point(130, 20),
                .Size = New Size(60, 60),
                .TextAlign = ContentAlignment.MiddleCenter,
                .BackColor = Color.Transparent
            }
            destinationCard.Controls.Add(iconLabel)

            Dim cityLabel As New Label With {
                .Text = destinations(i, 1),
                .Font = New Font("Segoe UI", 16, FontStyle.Bold),
                .Location = New Point(20, 90),
                .Size = New Size(280, 25),
                .TextAlign = ContentAlignment.MiddleCenter,
                .ForeColor = Color.FromArgb(70, 70, 70),
                .BackColor = Color.Transparent
            }
            destinationCard.Controls.Add(cityLabel)

            Dim descLabel As New Label With {
                .Text = destinations(i, 2),
                .Font = New Font("Segoe UI", 11),
                .Location = New Point(20, 120),
                .Size = New Size(280, 45),
                .TextAlign = ContentAlignment.MiddleCenter,
                .ForeColor = Color.FromArgb(120, 120, 120),
                .BackColor = Color.Transparent
            }
            destinationCard.Controls.Add(descLabel)

            destinationsPanel.Controls.Add(destinationCard)
        Next

        mainPanel.Controls.Add(destinationsPanel)
    End Sub

    Private Sub CreateFeaturesSection()
        featuresPanel = New Panel With {
            .Height = 350,
            .Dock = DockStyle.Top,
            .BackColor = Color.FromArgb(248, 249, 250),
            .Padding = New Padding(60)
        }

        Dim titleLabel As New Label With {
            .Text = "Why Choose Lakbay PH?",
            .Font = New Font("Segoe UI", 28, FontStyle.Bold),
            .ForeColor = Color.FromArgb(50, 50, 50),
            .AutoSize = True,
            .Location = New Point(60, 30)
        }
        featuresPanel.Controls.Add(titleLabel)

        ' Feature cards
        Dim features As String(,) = {
            {"🌍", "500+ Destinations", "Explore amazing places worldwide"},
            {"💎", "Premium Experiences", "Luxury travel and unique adventures"},
            {"🎯", "Expert Guidance", "Professional travel consultants"},
            {"🏆", "Award Winning", "Recognized for excellence in travel"}
        }

        For i As Integer = 0 To 3
            Dim featureCard As New Panel With {
                .Size = New Size(280, 160),
                .Location = New Point(60 + (i * 300), 100),
                .BackColor = Color.White
            }
            AddHandler featureCard.Paint, AddressOf FeatureCard_Paint

            Dim iconLabel As New Label With {
                .Text = features(i, 0),
                .Font = New Font("Segoe UI", 36),
                .Location = New Point(115, 25),
                .Size = New Size(50, 50),
                .TextAlign = ContentAlignment.MiddleCenter,
                .BackColor = Color.Transparent
            }
            featureCard.Controls.Add(iconLabel)

            Dim titleLabel2 As New Label With {
                .Text = features(i, 1),
                .Font = New Font("Segoe UI", 14, FontStyle.Bold),
                .Location = New Point(20, 85),
                .Size = New Size(240, 25),
                .TextAlign = ContentAlignment.MiddleCenter,
                .ForeColor = Color.FromArgb(70, 70, 70),
                .BackColor = Color.Transparent
            }
            featureCard.Controls.Add(titleLabel2)

            Dim descLabel As New Label With {
                .Text = features(i, 2),
                .Font = New Font("Segoe UI", 10),
                .Location = New Point(20, 115),
                .Size = New Size(240, 35),
                .TextAlign = ContentAlignment.MiddleCenter,
                .ForeColor = Color.FromArgb(120, 120, 120),
                .BackColor = Color.Transparent
            }
            featureCard.Controls.Add(descLabel)

            featuresPanel.Controls.Add(featureCard)
        Next

        mainPanel.Controls.Add(featuresPanel)
    End Sub

    Private Sub CreateFooter()
        footerPanel = New Panel With {
            .Height = 120,
            .Dock = DockStyle.Bottom,
            .BackColor = Color.FromArgb(40, 40, 40)
        }

        Dim footerLabel As New Label With {
            .Text = "© 2024 Lakbay Ph Travel & Tours. All rights reserved.",
            .Font = New Font("Segoe UI", 11),
            .ForeColor = Color.White,
            .AutoSize = True,
            .Location = New Point(60, 50)
        }
        footerPanel.Controls.Add(footerLabel)

        Dim contactLabel As New Label With {
            .Text = "📧 info@lakbayph.com | 📞 +639099624320-LAKBAYS | 🌐 www.lakbayph.com",
            .Font = New Font("Segoe UI", 11),
            .ForeColor = Color.FromArgb(200, 200, 200),
            .AutoSize = True,
            .Location = New Point(500, 50)
        }
        footerPanel.Controls.Add(contactLabel)

        Me.Controls.Add(footerPanel)
    End Sub

    ' NEW METHOD: Update UI for logged-in user
    Private Sub UpdateUIForLoggedInUser()
        If user IsNot Nothing Then
            ' Find the sign up button and change it to a welcome message or logout button
            For Each control As Control In headerPanel.Controls
                If TypeOf control Is Button AndAlso control.Tag IsNot Nothing AndAlso control.Tag.ToString() = "signUpButton" Then
                    Dim signUpButton As Button = CType(control, Button)
                    signUpButton.Text = $"👋 Welcome, {user.FirstName}!"
                    signUpButton.BackColor = Color.FromArgb(52, 152, 219)
                    ' Remove the click handler for sign up and add logout handler
                    RemoveHandler signUpButton.Click, AddressOf SignUpButton_Click
                    AddHandler signUpButton.Click, AddressOf WelcomeButton_Click
                    Exit For
                End If
            Next
        End If
    End Sub

    ' NEW METHOD: Handle welcome button click (for logged-in users)
    Private Sub WelcomeButton_Click(sender As Object, e As EventArgs)
        Dim result As DialogResult = MessageBox.Show($"Hello {user.FirstName}! Would you like to logout?", "User Menu", MessageBoxButtons.YesNo, MessageBoxIcon.Question)
        If result = DialogResult.Yes Then
            ' Logout logic - return to main form
            Me.Close()
            Dim mainForm As New MainForm()
            mainForm.Show()
        End If
    End Sub

    Private Sub SetupAnimations()
        fadeTimer = New Timer With {
            .Interval = 4000,
            .Enabled = True
        }
        AddHandler fadeTimer.Tick, AddressOf FadeTimer_Tick

        slideTimer = New Timer With {
            .Interval = 50,
            .Enabled = True
        }
        AddHandler slideTimer.Tick, AddressOf SlideTimer_Tick
    End Sub

    ' Event handlers
    Private Sub HeaderPanel_Paint(sender As Object, e As PaintEventArgs)
        Dim rect As New Rectangle(0, 0, headerPanel.Width, headerPanel.Height)
        Dim currentColor As Color = backgroundColors(currentImageIndex)
        Dim nextColor As Color = backgroundColors((currentImageIndex + 1) Mod backgroundColors.Length)

        Using brush As New LinearGradientBrush(rect, currentColor, nextColor, LinearGradientMode.Horizontal)
            e.Graphics.FillRectangle(brush, rect)
        End Using
    End Sub

    Private Sub DestinationCard_Paint(sender As Object, e As PaintEventArgs)
        Dim rect As New Rectangle(0, 0, sender.Width - 1, sender.Height - 1)
        Using path As GraphicsPath = CreateRoundedRectangle(rect, 12)
            e.Graphics.SmoothingMode = SmoothingMode.AntiAlias
            e.Graphics.FillPath(New SolidBrush(Color.White), path)
            e.Graphics.DrawPath(New Pen(Color.FromArgb(220, 220, 220), 2), path)
        End Using
    End Sub

    Private Sub ExploreButton_Paint(sender As Object, e As PaintEventArgs)
        Dim rect As New Rectangle(0, 0, sender.Width, sender.Height)
        Using brush As New LinearGradientBrush(rect, Color.FromArgb(255, 140, 0), Color.FromArgb(255, 100, 0), LinearGradientMode.Vertical)
            e.Graphics.FillRectangle(brush, rect)
        End Using
    End Sub

    Private Sub SignUpButton_Paint(sender As Object, e As PaintEventArgs)
        Dim rect As New Rectangle(0, 0, sender.Width, sender.Height)
        Using brush As New LinearGradientBrush(rect, Color.FromArgb(46, 204, 113), Color.FromArgb(39, 174, 96), LinearGradientMode.Vertical)
            e.Graphics.FillRectangle(brush, rect)
        End Using
    End Sub

    Private Sub FeatureCard_Paint(sender As Object, e As PaintEventArgs)
        Dim rect As New Rectangle(0, 0, sender.Width - 1, sender.Height - 1)
        Using path As GraphicsPath = CreateRoundedRectangle(rect, 10)
            e.Graphics.SmoothingMode = SmoothingMode.AntiAlias
            e.Graphics.FillPath(New SolidBrush(Color.White), path)
            e.Graphics.DrawPath(New Pen(Color.FromArgb(230, 230, 230), 1), path)
        End Using
    End Sub

    Private Sub ExploreButton_Click(sender As Object, e As EventArgs)
        Try
            Dim lakbayForm As New LakbayPHPackagesForm()
            lakbayForm.Show()
        Catch ex As Exception
            MessageBox.Show("Unable to open LakbayPH Packages form. Please ensure the form exists in your project.", "Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
        End Try
    End Sub

    Private Sub SignUpButton_Click(sender As Object, e As EventArgs)
        Try
            Dim signUpForm As New SignUpForm()
            signUpForm.Show()
        Catch ex As Exception
            MessageBox.Show("Unable to open Sign Up form. Please ensure the SignUpForm exists in your project.", "Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
        End Try
    End Sub

    Private Sub NavButton_Click(sender As Object, e As EventArgs)
        Dim btn As Button = CType(sender, Button)
        MessageBox.Show($"Welcome to the {btn.Text} section!" & vbCrLf & "Discover amazing travel experiences!", "Navigation", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Private Sub DestinationCard_Click(sender As Object, e As EventArgs)
        Dim panel As Panel = CType(sender, Panel)
        Dim cityLabel As Label = CType(panel.Controls(1), Label)
        MessageBox.Show($"Explore the wonders of {cityLabel.Text}!" & vbCrLf & vbCrLf & "Immerse yourself in local culture, cuisine, and unforgettable experiences!", "Destination Info", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Private Sub NavButton_MouseEnter(sender As Object, e As EventArgs)
        Dim btn As Button = CType(sender, Button)
        btn.ForeColor = Color.FromArgb(255, 200, 100)
    End Sub

    Private Sub NavButton_MouseLeave(sender As Object, e As EventArgs)
        Dim btn As Button = CType(sender, Button)
        btn.ForeColor = Color.White
    End Sub

    Private Sub DestinationCard_MouseEnter(sender As Object, e As EventArgs)
        Dim panel As Panel = CType(sender, Panel)
        panel.BackColor = Color.FromArgb(250, 250, 250)
    End Sub

    Private Sub DestinationCard_MouseLeave(sender As Object, e As EventArgs)
        Dim panel As Panel = CType(sender, Panel)
        panel.BackColor = Color.White
    End Sub

    Private Sub SignUpButton_MouseEnter(sender As Object, e As EventArgs)
        Dim btn As Button = CType(sender, Button)
        btn.BackColor = Color.FromArgb(39, 174, 96)
    End Sub

    Private Sub SignUpButton_MouseLeave(sender As Object, e As EventArgs)
        Dim btn As Button = CType(sender, Button)
        btn.BackColor = Color.FromArgb(46, 204, 113)
    End Sub

    Private Sub FadeTimer_Tick(sender As Object, e As EventArgs)
        currentImageIndex = (currentImageIndex + 1) Mod backgroundImages.Length
        headerPanel.Invalidate()
    End Sub

    Private Sub SlideTimer_Tick(sender As Object, e As EventArgs)
        slideOffset = (slideOffset + 1) Mod 100
        ' You can add subtle animation effects here
    End Sub

    ' Helper method for rounded rectangles
    Private Function CreateRoundedRectangle(rect As Rectangle, radius As Integer) As GraphicsPath
        Dim path As New GraphicsPath()
        path.AddArc(rect.X, rect.Y, radius, radius, 180, 90)
        path.AddArc(rect.Right - radius, rect.Y, radius, radius, 270, 90)
        path.AddArc(rect.Right - radius, rect.Bottom - radius, radius, radius, 0, 90)
        path.AddArc(rect.X, rect.Bottom - radius, radius, radius, 90, 90)
        path.CloseAllFigures()
        Return path
    End Function

    Private Sub TravelHomepageForm_Load(sender As Object, e As EventArgs) Handles MyBase.Load
        ' Any additional initialization can go here
    End Sub

    ' Property to get current user
    Public ReadOnly Property CurrentUser As MainForm.UserInfo
        Get
            Return user
        End Get
    End Property
End Class

' Program entry point - Only use this if TravelHomepageForm is your startup form
' If MainForm is your startup form, remove this module
Module Program
    <STAThread>
    Sub Main()
        Application.EnableVisualStyles()
        Application.SetCompatibleTextRenderingDefault(False)
        ' Start with MainForm instead of TravelHomepageForm
        Application.Run(New MainForm())
    End Sub
End Module