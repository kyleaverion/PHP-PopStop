Imports System.Drawing
Imports System.Windows.Forms
Imports System.Drawing.Drawing2D

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
        Me.BackColor = Color.FromArgb(44, 95, 97)
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
        ' Set form background
        AddHandler Me.Paint, AddressOf Form_Paint
        AddHandler Me.Resize, AddressOf Form_Resize


    End Sub

    Private Sub Form_Paint(sender As Object, e As PaintEventArgs)
        ' Create ocean/tropical background
        Dim rect As New Rectangle(0, 0, Me.Width, Me.Height)
        Dim brush As New LinearGradientBrush(rect, Color.FromArgb(0, 150, 136), Color.FromArgb(26, 188, 156), LinearGradientMode.Vertical)
        e.Graphics.FillRectangle(brush, rect)
        brush.Dispose()
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
            .BackColor = Color.Transparent
            AddHandler Me.Paint, AddressOf BackgroundPanel_Paint
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
            .BackColor = Color.White
            .BorderStyle = BorderStyle.None
            AddHandler Me.Paint, AddressOf DomesticPanel_Paint

            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf PnlDomestic_Click
            AddHandler .MouseEnter, AddressOf PackageCard_MouseEnter
            AddHandler .MouseLeave, AddressOf PackageCard_MouseLeave
        End With

        ' Domestic Label
        With Me.lblDomestic
            .Text = "DOMESTIC"
            .Font = New Font("Segoe UI", 32, FontStyle.Bold)
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
            .BackColor = Color.White
            .BorderStyle = BorderStyle.None
            AddHandler Me.Paint, AddressOf InternationalPanel_Paint

            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf PnlInternational_Click
            AddHandler .MouseEnter, AddressOf PackageCard_MouseEnter
            AddHandler .MouseLeave, AddressOf PackageCard_MouseLeave
        End With

        ' International Label
        With Me.lblInternational
            .Text = "INTERNATIONAL"
            .Font = New Font("Segoe UI", 28, FontStyle.Bold)
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
            .BackColor = Color.White
            .BorderStyle = BorderStyle.None
            AddHandler Me.Paint, AddressOf FreedivingPanel_Paint

            .Cursor = Cursors.Hand
            AddHandler .Click, AddressOf PnlFreediving_Click
            AddHandler .MouseEnter, AddressOf PackageCard_MouseEnter
            AddHandler .MouseLeave, AddressOf PackageCard_MouseLeave
        End With

        ' Freediving Label
        With Me.lblFreediving
            .Text = "FREEDIVING"
            .Font = New Font("Segoe UI", 32, FontStyle.Bold)
            .ForeColor = Color.Black
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

    Private Sub BackgroundPanel_Paint(sender As Object, e As PaintEventArgs)
        ' Create tropical ocean background
        Dim rect As New Rectangle(0, 0, pnlBackground.Width, pnlBackground.Height)
        Dim brush As New LinearGradientBrush(rect, Color.FromArgb(0, 150, 136), Color.FromArgb(26, 188, 156), LinearGradientMode.Vertical)
        e.Graphics.FillRectangle(brush, rect)
        brush.Dispose()
    End Sub

    Private Sub DomesticPanel_Paint(sender As Object, e As PaintEventArgs)
        ' Create rounded rectangle with domestic background
        Dim rect As New Rectangle(0, 0, pnlDomestic.Width, pnlDomestic.Height)
        Dim path As GraphicsPath = CreateRoundedRectangle(rect, 20)

        ' Gradient background (tropical sunset)
        Dim brush As New LinearGradientBrush(rect, Color.FromArgb(255, 154, 158), Color.FromArgb(250, 208, 196), LinearGradientMode.Vertical)
        e.Graphics.FillPath(brush, path)
        brush.Dispose()

        ' Add border
        Using pen As New Pen(Color.White, 3)
            e.Graphics.DrawPath(pen, path)
        End Using

        path.Dispose()
    End Sub

    Private Sub InternationalPanel_Paint(sender As Object, e As PaintEventArgs)
        ' Create rounded rectangle with international background
        Dim rect As New Rectangle(0, 0, pnlInternational.Width, pnlInternational.Height)
        Dim path As GraphicsPath = CreateRoundedRectangle(rect, 20)

        ' Gradient background (Paris sky)
        Dim brush As New LinearGradientBrush(rect, Color.FromArgb(200, 200, 220), Color.FromArgb(250, 250, 255), LinearGradientMode.Vertical)
        e.Graphics.FillPath(brush, path)
        brush.Dispose()

        ' Add border
        Using pen As New Pen(Color.White, 3)
            e.Graphics.DrawPath(pen, path)
        End Using

        path.Dispose()
    End Sub

    Private Sub FreedivingPanel_Paint(sender As Object, e As PaintEventArgs)
        ' Create rounded rectangle with freediving background
        Dim rect As New Rectangle(0, 0, pnlFreediving.Width, pnlFreediving.Height)
        Dim path As GraphicsPath = CreateRoundedRectangle(rect, 20)

        ' Gradient background (deep ocean)
        Dim brush As New LinearGradientBrush(rect, Color.FromArgb(30, 60, 120), Color.FromArgb(70, 130, 180), LinearGradientMode.Vertical)
        e.Graphics.FillPath(brush, path)
        brush.Dispose()

        ' Add border
        Using pen As New Pen(Color.White, 3)
            e.Graphics.DrawPath(pen, path)
        End Using

        path.Dispose()
    End Sub

    Private Function CreateRoundedRectangle(rect As Rectangle, radius As Integer) As GraphicsPath
        Dim path As New GraphicsPath()
        path.AddArc(rect.X, rect.Y, radius, radius, 180, 90)
        path.AddArc(rect.X + rect.Width - radius, rect.Y, radius, radius, 270, 90)
        path.AddArc(rect.X + rect.Width - radius, rect.Y + rect.Height - radius, radius, radius, 0, 90)
        path.AddArc(rect.X, rect.Y + rect.Height - radius, radius, radius, 90, 90)
        path.CloseAllFigures()
        Return path
    End Function

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
        MessageBox.Show("Navigating to Home page...", "Navigation", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Private Sub BtnAboutUs_Click(sender As Object, e As EventArgs)
        MessageBox.Show("Navigating to About Us page...", "Navigation", MessageBoxButtons.OK, MessageBoxIcon.Information)
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