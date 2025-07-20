Imports System.Drawing
Imports System.Windows.Forms

Public Class LakbayPHPackagesForm
    Inherits Form

    Private components As System.ComponentModel.IContainer

    ' Navigation controls
    Private pnlNavigation As Panel
    Private lblLogo As Label
    Private btnHome As Button
    Private btnPackages As Button
    Private btnAboutUs As Button
    Private btnUserProfile As Button
    Private btnMenu As Button

    ' Package cards
    Private pnlDomestic As Panel
    Private pnlInternational As Panel
    Private pnlFreediving As Panel
    Private lblDomestic As Label
    Private lblInternational As Label
    Private lblFreediving As Label

    ' Background panel
    Private pnlBackground As Panel

    Public Sub New()
        InitializeComponent()
        SetupForm()
        SetupNavigation()
        SetupPackageCards()
    End Sub

    Private Sub InitializeComponent()
        Me.components = New System.ComponentModel.Container()
        Me.SuspendLayout()

        ' Form settings
        Me.Text = "LakbayPH - Travel Packages"
        Me.Size = New Size(1400, 800)
        Me.StartPosition = FormStartPosition.CenterScreen
        Me.FormBorderStyle = FormBorderStyle.Sizable
        Me.BackColor = Color.FromArgb(26, 188, 156) ' Ocean blue-green
        Me.WindowState = FormWindowState.Maximized

        ' Initialize controls
        Me.pnlNavigation = New Panel()
        Me.lblLogo = New Label()
        Me.btnHome = New Button()
        Me.btnPackages = New Button()
        Me.btnAboutUs = New Button()
        Me.btnUserProfile = New Button()
        Me.btnMenu = New Button()
        Me.pnlBackground = New Panel()
        Me.pnlDomestic = New Panel()
        Me.pnlInternational = New Panel()
        Me.pnlFreediving = New Panel()
        Me.lblDomestic = New Label()
        Me.lblInternational = New Label()
        Me.lblFreediving = New Label()

        ' Add controls to form
        Me.Controls.Add(Me.pnlNavigation)
        Me.Controls.Add(Me.pnlBackground)

        Me.ResumeLayout(False)
        Me.PerformLayout()
    End Sub

    Private Sub SetupForm()
        ' Form resize handler
        AddHandler Me.Resize, AddressOf Form_Resize
    End Sub

    Private Sub Form_Resize(sender As Object, e As EventArgs)
        ' Reposition elements on resize
        If Me.WindowState = FormWindowState.Maximized Then
            ResizeComponents()
        End If
    End Sub

    Private Sub ResizeComponents()
        ' Adjust component sizes based on form size
        If pnlBackground IsNot Nothing Then
            pnlBackground.Size = New Size(Me.ClientSize.Width, Me.ClientSize.Height - 70)
            RepositionPackageCards()
        End If
    End Sub

    Private Sub SetupNavigation()
        ' Navigation Panel
        With Me.pnlNavigation
            .Location = New Point(0, 0)
            .Size = New Size(Me.Width, 70)
            .BackColor = Color.White
            .Dock = DockStyle.Top
            .BorderStyle = BorderStyle.None
        End With

        ' Logo
        With Me.lblLogo
            .Text = "🌊 LakbayPH" & vbCrLf & "Travel & Tours"
            .Font = New Font("Segoe UI", 12, FontStyle.Bold)
            .ForeColor = Color.FromArgb(44, 95, 97)
            .Location = New Point(20, 10)
            .Size = New Size(200, 50)
            .BackColor = Color.Transparent
            .TextAlign = ContentAlignment.MiddleLeft
        End With

        ' Home Button
        With Me.btnHome
            .Text = "Home"
            .Font = New Font("Segoe UI", 11)
            .Location = New Point(Me.Width - 450, 20)
            .Size = New Size(80, 30)
            .BackColor = Color.Transparent
            .ForeColor = Color.FromArgb(100, 100, 100)
            .FlatStyle = FlatStyle.Flat
            .FlatAppearance.BorderSize = 0
            .FlatAppearance.MouseOverBackColor = Color.FromArgb(240, 240, 240)
            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf BtnHome_Click
        End With

        ' Packages Button (Active)
        With Me.btnPackages
            .Text = "Packages"
            .Font = New Font("Segoe UI", 11, FontStyle.Bold)
            .Location = New Point(Me.Width - 350, 20)
            .Size = New Size(80, 30)
            .BackColor = Color.FromArgb(100, 200, 255)
            .ForeColor = Color.White
            .FlatStyle = FlatStyle.Flat
            .FlatAppearance.BorderSize = 0
            .Cursor = Cursors.Hand
        End With

        ' About Us Button
        With Me.btnAboutUs
            .Text = "About Us"
            .Font = New Font("Segoe UI", 11)
            .Location = New Point(Me.Width - 250, 20)
            .Size = New Size(80, 30)
            .BackColor = Color.Transparent
            .ForeColor = Color.FromArgb(100, 100, 100)
            .FlatStyle = FlatStyle.Flat
            .FlatAppearance.BorderSize = 0
            .FlatAppearance.MouseOverBackColor = Color.FromArgb(240, 240, 240)
            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf BtnAboutUs_Click
        End With

        ' User Profile Button
        With Me.btnUserProfile
            .Text = "👤"
            .Font = New Font("Segoe UI", 16)
            .Location = New Point(Me.Width - 150, 20)
            .Size = New Size(40, 30)
            .BackColor = Color.FromArgb(44, 95, 97)
            .ForeColor = Color.White
            .FlatStyle = FlatStyle.Flat
            .FlatAppearance.BorderSize = 0
            .FlatAppearance.MouseOverBackColor = Color.FromArgb(60, 110, 112)
            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf BtnUserProfile_Click
        End With

        ' Menu Button
        With Me.btnMenu
            .Text = "☰"
            .Font = New Font("Segoe UI", 14)
            .Location = New Point(Me.Width - 90, 20)
            .Size = New Size(40, 30)
            .BackColor = Color.Transparent
            .ForeColor = Color.FromArgb(100, 100, 100)
            .FlatStyle = FlatStyle.Flat
            .FlatAppearance.BorderSize = 0
            .FlatAppearance.MouseOverBackColor = Color.FromArgb(240, 240, 240)
            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf BtnMenu_Click
        End With

        ' Add navigation controls to panel
        Me.pnlNavigation.Controls.Add(Me.lblLogo)
        Me.pnlNavigation.Controls.Add(Me.btnHome)
        Me.pnlNavigation.Controls.Add(Me.btnPackages)
        Me.pnlNavigation.Controls.Add(Me.btnAboutUs)
        Me.pnlNavigation.Controls.Add(Me.btnUserProfile)
        Me.pnlNavigation.Controls.Add(Me.btnMenu)
    End Sub

    Private Sub SetupPackageCards()
        ' Background Panel
        With Me.pnlBackground
            .Location = New Point(0, 70)
            .Size = New Size(Me.Width, Me.Height - 70)
            .BackColor = Color.FromArgb(0, 150, 136) ' Darker ocean color
        End With

        ' Calculate card positions
        Dim cardWidth As Integer = 350
        Dim cardHeight As Integer = 500
        Dim spacing As Integer = 50
        Dim startX As Integer = (Me.Width - (3 * cardWidth + 2 * spacing)) \ 2
        Dim startY As Integer = 100

        ' Domestic Package Card
        With Me.pnlDomestic
            .Location = New Point(startX, startY)
            .Size = New Size(cardWidth, cardHeight)
            .BackColor = Color.FromArgb(255, 154, 158) ' Tropical sunset pink
            .BorderStyle = BorderStyle.None
            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf PnlDomestic_Click
            AddHandler .MouseEnter, AddressOf PackageCard_MouseEnter
            AddHandler .MouseLeave, AddressOf PackageCard_MouseLeave
        End With

        ' Domestic Label
        With Me.lblDomestic
            .Text = "DOMESTIC"
            .Font = New Font("Segoe UI", 24, FontStyle.Bold)
            .ForeColor = Color.Black
            .Location = New Point(30, 350)
            .Size = New Size(290, 50)
            .BackColor = Color.Transparent
            .TextAlign = ContentAlignment.MiddleLeft
        End With

        ' International Package Card
        With Me.pnlInternational
            .Location = New Point(startX + cardWidth + spacing, startY)
            .Size = New Size(cardWidth, cardHeight)
            .BackColor = Color.FromArgb(200, 200, 220) ' Light purple-gray for Paris
            .BorderStyle = BorderStyle.None
            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf PnlInternational_Click
            AddHandler .MouseEnter, AddressOf PackageCard_MouseEnter
            AddHandler .MouseLeave, AddressOf PackageCard_MouseLeave
        End With

        ' International Label
        With Me.lblInternational
            .Text = "INTERNATIONAL"
            .Font = New Font("Segoe UI", 24, FontStyle.Bold)
            .ForeColor = Color.FromArgb(50, 50, 50)
            .Location = New Point(30, 350)
            .Size = New Size(290, 50)
            .BackColor = Color.Transparent
            .TextAlign = ContentAlignment.MiddleLeft
        End With

        ' Freediving Package Card
        With Me.pnlFreediving
            .Location = New Point(startX + 2 * (cardWidth + spacing), startY)
            .Size = New Size(cardWidth, cardHeight)
            .BackColor = Color.FromArgb(30, 60, 120) ' Deep ocean blue
            .BorderStyle = BorderStyle.None
            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf PnlFreediving_Click
            AddHandler .MouseEnter, AddressOf PackageCard_MouseEnter
            AddHandler .MouseLeave, AddressOf PackageCard_MouseLeave
        End With

        ' Freediving Label
        With Me.lblFreediving
            .Text = "FREEDIVING"
            .Font = New Font("Segoe UI", 24, FontStyle.Bold)
            .ForeColor = Color.White ' White text for contrast on dark blue
            .Location = New Point(30, 350)
            .Size = New Size(290, 50)
            .BackColor = Color.Transparent
            .TextAlign = ContentAlignment.MiddleLeft
        End With

        ' Add cards to background panel
        Me.pnlBackground.Controls.Add(Me.pnlDomestic)
        Me.pnlBackground.Controls.Add(Me.pnlInternational)
        Me.pnlBackground.Controls.Add(Me.pnlFreediving)
        Me.pnlDomestic.Controls.Add(Me.lblDomestic)
        Me.pnlInternational.Controls.Add(Me.lblInternational)
        Me.pnlFreediving.Controls.Add(Me.lblFreediving)
    End Sub

    Private Sub RepositionPackageCards()
        Dim cardWidth As Integer = 350
        Dim cardHeight As Integer = 500
        Dim spacing As Integer = 50
        Dim startX As Integer = (Me.ClientSize.Width - (3 * cardWidth + 2 * spacing)) \ 2
        Dim startY As Integer = 100

        pnlDomestic.Location = New Point(startX, startY)
        pnlInternational.Location = New Point(startX + cardWidth + spacing, startY)
        pnlFreediving.Location = New Point(startX + 2 * (cardWidth + spacing), startY)
    End Sub

    ' Event handlers for card interactions
    Private Sub PackageCard_MouseEnter(sender As Object, e As EventArgs)
        Dim panel As Panel = DirectCast(sender, Panel)
        panel.Location = New Point(panel.Location.X, panel.Location.Y - 10)
    End Sub

    Private Sub PackageCard_MouseLeave(sender As Object, e As EventArgs)
        Dim panel As Panel = DirectCast(sender, Panel)
        panel.Location = New Point(panel.Location.X, panel.Location.Y + 10)
    End Sub

    ' Navigation event handlers
    Private Sub BtnHome_Click(sender As Object, e As EventArgs)
        Dim travelerHome As New TravelHomepageForm()
        travelerHome.Show()
        Me.Close()
    End Sub

    Private Sub BtnAboutUs_Click(sender As Object, e As EventArgs)
        Dim aboutUs As New AboutUsFormwou()
        aboutUs.Show()
    End Sub

    Private Sub BtnUserProfile_Click(sender As Object, e As EventArgs)
        MessageBox.Show("Opening User Profile...", "User Profile", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Private Sub BtnMenu_Click(sender As Object, e As EventArgs)
        MessageBox.Show("Opening Menu...", "Menu", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    ' Package card event handlers
    Private Sub PnlDomestic_Click(sender As Object, e As EventArgs)
        MessageBox.Show("Opening Domestic Packages..." & vbCrLf & "Explore beautiful destinations within the Philippines!", "Domestic Packages", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Private Sub PnlInternational_Click(sender As Object, e As EventArgs)
        MessageBox.Show("Opening International Packages..." & vbCrLf & "Discover amazing destinations around the world!", "International Packages", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Private Sub PnlFreediving_Click(sender As Object, e As EventArgs)
        MessageBox.Show("Opening Freediving Packages..." & vbCrLf & "Dive into underwater adventures!", "Freediving Packages", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Protected Overrides Sub Dispose(disposing As Boolean)
        If disposing AndAlso (components IsNot Nothing) Then
            components.Dispose()
        End If
        MyBase.Dispose(disposing)
    End Sub
End Class

' Module to run the packages form
Module PackagesProgram
    Sub Main()
        Application.EnableVisualStyles()
        Application.SetCompatibleTextRenderingDefault(False)
        Application.Run(New LakbayPHPackagesForm())
    End Sub
End Module